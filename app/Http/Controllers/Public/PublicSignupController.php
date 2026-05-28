<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSignupRequest;
use App\Jobs\SendSignupConfirmationEmailJob;
use App\Models\TenantSignup;
use App\Models\TermsAcceptance;
use App\Services\RecaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Cadastro público self-service de oficinas (PRD 03, Epic 4, Story 4.2).
 *
 * Rotas vivem no subdomínio principal (app.iaqueatende.com.br), fora do
 * grupo com middleware 'tenant'/'auth'.
 */
class PublicSignupController extends Controller
{
    public function __construct(private RecaptchaService $recaptcha) {}

    /**
     * AC1 — Renderiza o formulário público de cadastro.
     */
    public function create()
    {
        return view('public.cadastro', [
            'recaptchaSiteKey' => config('pitstop.recaptcha.site_key'),
        ]);
    }

    /**
     * AC3 — Validação real-time de slug. Retorna JSON {status, mensagem}.
     */
    public function verificarSlug(Request $request): JsonResponse
    {
        $slug = mb_strtolower(trim((string) $request->query('slug', '')));

        // Formato inválido (AC4 / regra de slug).
        if (! preg_match('/^[a-z0-9]([a-z0-9-]{1,28})[a-z0-9]$/', $slug)) {
            return response()->json([
                'status' => 'invalido',
                'mensagem' => 'Use de 3 a 30 caracteres: letras minúsculas, números e hífen (sem começar/terminar com hífen).',
            ]);
        }

        // Reservado (AC4).
        if (in_array($slug, config('pitstop.slugs_reservados', []), true)) {
            return response()->json([
                'status' => 'indisponivel',
                'mensagem' => 'Este endereço é reservado. Escolha outro.',
            ]);
        }

        // CON-001: checa tenants ativos E signups pendentes.
        $emTenants = DB::table('tenants')->where('slug', $slug)->exists();
        $emSignups = DB::table('tenant_signups')
            ->where('slug_desejado', $slug)
            ->where('status', '!=', TenantSignup::STATUS_EXPIRED)
            ->exists();

        if ($emTenants || $emSignups) {
            return response()->json([
                'status' => 'indisponivel',
                'mensagem' => 'Este endereço já está em uso. Escolha outro.',
            ]);
        }

        return response()->json([
            'status' => 'disponivel',
            'mensagem' => 'Endereço disponível!',
        ]);
    }

    /**
     * AC5, AC7, AC8, AC10 — Processa o cadastro.
     */
    public function store(StoreSignupRequest $request)
    {
        // AC10 — reCAPTCHA v3 (bypass em dev).
        if (! $this->recaptcha->verificar($request->input('recaptcha_token'), $request->ip())) {
            return back()
                ->withInput($request->except('senha', 'senha_confirmation'))
                ->withErrors(['recaptcha' => 'Falha na verificação de segurança. Tente novamente.']);
        }

        $validated = $request->validated();

        $signup = DB::transaction(function () use ($request, $validated) {
            // AC7 — cria signup pendente com token UUID v4 e expiração 24h.
            $signup = TenantSignup::create([
                'nome_oficina' => $validated['nome_oficina'],
                'slug_desejado' => $validated['slug_desejado'],
                'cnpj' => $validated['cnpj'] ?? null,
                'telefone' => $validated['telefone'],
                'cidade' => $validated['cidade'],
                'uf' => $validated['uf'],
                'nome_completo' => $validated['nome_completo'],
                'email' => $validated['email'],
                'senha_hash' => Hash::make($validated['senha']),
                'plano_escolhido' => TenantSignup::PLANO_TRIAL,
                'consentimento_emails_transacionais' => true,
                'consentimento_marketing' => $request->boolean('consentimento_marketing'),
                'token_confirmacao' => (string) Str::uuid(),
                'token_expira_em' => now()->addHours((int) config('pitstop.signup.token_validade_horas', 24)),
                'status' => TenantSignup::STATUS_PENDING,
                'ip_origem' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);

            // Registro imutável de aceite (LGPD).
            $versao = config('pitstop.signup.versao_termos', '1.0');
            foreach ([TermsAcceptance::TIPO_TERMOS_USO, TermsAcceptance::TIPO_PRIVACIDADE] as $tipo) {
                TermsAcceptance::create([
                    'tenant_signup_id' => $signup->id,
                    'tipo' => $tipo,
                    'versao' => $versao,
                    'ip' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 1000),
                ]);
            }
            if ($request->boolean('consentimento_marketing')) {
                TermsAcceptance::create([
                    'tenant_signup_id' => $signup->id,
                    'tipo' => TermsAcceptance::TIPO_MARKETING,
                    'versao' => $versao,
                    'ip' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 1000),
                ]);
            }

            return $signup;
        });

        // AC8 — dispara e-mail de confirmação via fila.
        SendSignupConfirmationEmailJob::dispatch($signup);

        // Story 4.8 — notifica admin sobre novo cadastro
        \App\Jobs\NotifyAdminNewSignupJob::dispatch($signup->id);

        // Guarda o e-mail em sessão para a tela de confirmação / reenvio.
        $request->session()->put('signup_email', $signup->email);

        return redirect()->route('cadastro.confirmacao');
    }

    /**
     * AC9 — Tela "Verifique seu email".
     */
    public function confirmacao(Request $request)
    {
        $email = $request->session()->get('signup_email');

        if (! $email) {
            return redirect()->route('cadastro.form');
        }

        return view('public.cadastro-confirmacao', ['email' => $email]);
    }

    /**
     * AC9 — Reenvio do e-mail de confirmação.
     * Rate-limit: 1 reenvio/60s, máx 3/hora (controlado via Cache).
     */
    public function reenviarEmail(Request $request)
    {
        $email = $request->session()->get('signup_email');

        if (! $email) {
            return redirect()->route('cadastro.form');
        }

        $chaveCooldown = 'signup:resend:cooldown:'.md5($email);
        $chaveHora = 'signup:resend:hour:'.md5($email);

        if (Cache::has($chaveCooldown)) {
            return back()->with('error', 'Aguarde alguns segundos antes de reenviar o e-mail.');
        }

        $tentativasHora = (int) Cache::get($chaveHora, 0);
        if ($tentativasHora >= 3) {
            return back()->with('error', 'Limite de reenvios atingido. Tente novamente mais tarde.');
        }

        $signup = TenantSignup::where('email', $email)
            ->where('status', TenantSignup::STATUS_PENDING)
            ->latest('id')
            ->first();

        if (! $signup) {
            return back()->with('error', 'Cadastro não encontrado ou já confirmado.');
        }

        // Renova o token se já expirou (link sempre válido por 24h ao reenviar).
        if ($signup->token_expira_em && $signup->token_expira_em->isPast()) {
            $signup->update([
                'token_confirmacao' => (string) Str::uuid(),
                'token_expira_em' => now()->addHours((int) config('pitstop.signup.token_validade_horas', 24)),
            ]);
        }

        SendSignupConfirmationEmailJob::dispatch($signup);

        Cache::put($chaveCooldown, true, now()->addSeconds(60));
        Cache::put($chaveHora, $tentativasHora + 1, now()->addHour());

        return back()->with('success', 'E-mail de confirmação reenviado!');
    }
}
