<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantSignup;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Confirmação de e-mail + provisionamento atômico do tenant (PRD 03, Story 4.3).
 *
 * Rota pública GET /confirmar-email/{token}, vive no subdomínio principal
 * (app.iaqueatende.com.br), fora do grupo com middleware 'tenant'/'auth'.
 *
 * Ao validar o token, executa uma transação atômica (NFR-005) que cria
 * Tenant + User admin + Subscription e atualiza o tenant_signup. Qualquer
 * falha faz rollback completo — sem tenant fantasma, sem user órfão.
 */
class EmailConfirmationController extends Controller
{
    /**
     * AC1–AC9 — Confirma o e-mail e provisiona o tenant.
     *
     * @param  string  $token  Valor de tenant_signups.token_confirmacao (UUID v4).
     */
    public function confirm(string $token)
    {
        // T1.1 (AC1) — Busca o signup pelo token. semTenant() não é necessário:
        // TenantSignup não usa BelongsToTenant, mas o tenant ainda não existe.
        $signup = TenantSignup::where('token_confirmacao', $token)->first();

        // Token não encontrado (AC1) — tela de erro genérica.
        if (! $signup) {
            return response()
                ->view('public.erro-confirmacao', [
                    'titulo' => 'Link inválido',
                    'mensagem' => 'Este link de confirmação não foi encontrado. Verifique se você copiou o endereço completo do e-mail.',
                    'podeReenviar' => false,
                ], 404);
        }

        // T1.2 (AC8) — Token já utilizado: conta já ativada → login normal, sem 500.
        if ($signup->status === TenantSignup::STATUS_CONFIRMED) {
            return redirect()
                ->route('login')
                ->with('info', 'Esta conta já foi ativada. Faça login normalmente.');
        }

        // T1.2 (AC7) — Status precisa estar pendente para confirmar.
        if ($signup->status !== TenantSignup::STATUS_PENDING) {
            return response()->view('public.erro-confirmacao', [
                'titulo' => 'Cadastro indisponível',
                'mensagem' => 'Este cadastro não pode mais ser confirmado. Inicie um novo cadastro.',
                'podeReenviar' => false,
            ], 410);
        }

        // T1.2 (AC7) — Token expirado: oferece reenvio com e-mail mascarado.
        if ($signup->token_expira_em === null || $signup->token_expira_em->isPast()) {
            return response()->view('public.erro-confirmacao', [
                'titulo' => 'Link expirado',
                'mensagem' => 'Este link de confirmação expirou. Solicite um novo abaixo.',
                'podeReenviar' => true,
                'emailMascarado' => $this->mascararEmail($signup->email),
            ], 410);
        }

        // T1.3, T2, T3, T4.1 (AC2, AC3) — Provisionamento atômico.
        try {
            [$tenant, $user] = $this->provisionarTenant($signup);
        } catch (Throwable $e) {
            // AC3 — rollback já ocorreu (DB::transaction). Loga e mostra erro.
            Log::error('Falha no provisionamento de tenant via confirmação de e-mail', [
                'signup_id' => $signup->id,
                'erro' => $e->getMessage(),
            ]);

            return response()->view('public.erro-confirmacao', [
                'titulo' => 'Não foi possível criar sua conta',
                'mensagem' => 'Ocorreu um erro ao criar sua oficina. Nenhuma cobrança ou dado foi gerado. Entre em contato com o suporte para concluir seu cadastro.',
                'podeReenviar' => false,
                'mostrarSuporte' => true,
            ], 500);
        }

        // T4.2 (AC4) — Seed inicial fora da transação (fire-and-forget).
        // O job é criado na Story 4.4; dispara apenas se a classe já existir.
        $seedJob = 'App\\Jobs\\SeedTenantInitialDataJob';
        if (class_exists($seedJob)) {
            $seedJob::dispatch($tenant->id);
        }

        // T5.1 (AC5) — Login passivo no guard web.
        Auth::login($user);

        // T5.2 (AC6) — Redirect para o subdomínio do tenant / onboarding wizard.
        return redirect()->away($this->urlOnboarding($tenant));
    }

    /**
     * Executa a criação atômica de Tenant + Subscription + User e atualiza o signup.
     *
     * @return array{0: Tenant, 1: User}
     */
    private function provisionarTenant(TenantSignup $signup): array
    {
        $tenant = null;
        $user = null;

        DB::transaction(function () use ($signup, &$tenant, &$user) {
            // T2.1, T2.3 (AC2a, AC9) — Tenant com trial de 14 dias.
            $trialFim = now()->addDays((int) config('pitstop.signup.trial_dias', 14));

            $tenant = Tenant::create([
                'nome' => $signup->nome_oficina,
                'slug' => $signup->slug_desejado,
                'plano' => Subscription::PLANO_TRIAL,
                'ativo' => true,
                'trial_ends_at' => $trialFim,
            ]);

            // T2.2 (AC2) — Subscription trial vinculada ao tenant.
            Subscription::create([
                'tenant_id' => $tenant->id,
                'plano' => Subscription::PLANO_TRIAL,
                'status' => Subscription::STATUS_TRIAL,
                'trial_termina_em' => $trialFim,
                'gateway' => Subscription::GATEWAY_MANUAL,
            ]);

            // T3.1 (AC2b) — User admin. A senha JÁ vem hasheada do signup
            // (Story 4.2 fez Hash::make). forceFill evita re-hash pelo cast 'hashed'.
            $user = new User([
                'tenant_id' => $tenant->id,
                'name' => $signup->nome_completo,
                'username' => $signup->slug_desejado,
                'email' => $signup->email,
                'perfil' => 'admin',
                'ativo' => true,
            ]);
            $user->forceFill(['password' => $signup->senha_hash]);
            $user->save();

            // T4.1 (AC2c) — Atualiza o signup como confirmado.
            $signup->update([
                'status' => TenantSignup::STATUS_CONFIRMED,
                'tenant_id' => $tenant->id,
            ]);
        });

        return [$tenant, $user];
    }

    /**
     * Monta a URL de onboarding no subdomínio do tenant (AC6).
     */
    private function urlOnboarding(Tenant $tenant): string
    {
        // Deriva o domínio base a partir do app_url configurado
        // (ex: https://app.iaqueatende.com.br → iaqueatende.com.br).
        $appUrl = config('pitstop.signup.app_url', 'https://app.iaqueatende.com.br');
        $hostBase = preg_replace('#^https?://#', '', rtrim($appUrl, '/'));
        $partes = explode('.', $hostBase);
        // Remove o subdomínio "app" (ou outro) para obter o domínio raiz.
        $dominioRaiz = count($partes) > 2 ? implode('.', array_slice($partes, 1)) : $hostBase;

        return "https://{$tenant->slug}.{$dominioRaiz}/onboarding/wizard";
    }

    /**
     * Mascara o e-mail para exibição (AC7): joao@exemplo.com → j***@exemplo.com.
     */
    private function mascararEmail(string $email): string
    {
        $partes = explode('@', $email, 2);
        if (count($partes) !== 2 || $partes[0] === '') {
            return $email;
        }

        $local = $partes[0];
        $primeira = mb_substr($local, 0, 1);

        return $primeira.'***@'.$partes[1];
    }
}
