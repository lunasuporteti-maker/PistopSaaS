<?php

namespace Tests\Feature\Signup;

use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantSignup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailConfirmationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Cria um signup pendente pronto para confirmação.
     */
    private function criarSignupPendente(array $overrides = []): TenantSignup
    {
        return TenantSignup::create(array_merge([
            'nome_oficina' => 'Oficina do Zé',
            'slug_desejado' => 'oficina-do-ze',
            'telefone' => '(81) 99999-0000',
            'cidade' => 'Recife',
            'uf' => 'PE',
            'nome_completo' => 'José da Silva',
            'email' => 'jose@oficina.com',
            'senha_hash' => Hash::make('Senha123'),
            'plano_escolhido' => TenantSignup::PLANO_TRIAL,
            'consentimento_emails_transacionais' => true,
            'consentimento_marketing' => false,
            'token_confirmacao' => (string) Str::uuid(),
            'token_expira_em' => now()->addHours(24),
            'status' => TenantSignup::STATUS_PENDING,
        ], $overrides));
    }

    // T8.1 (AC2, AC4, AC5, AC6) — happy path: provisiona tudo e redireciona.
    public function test_confirmacao_valida_provisiona_tenant_user_subscription_e_redireciona(): void
    {
        Queue::fake();
        $signup = $this->criarSignupPendente();

        $response = $this->get('/confirmar-email/'.$signup->token_confirmacao);

        // AC6 — redirect para domínio único do app / onboarding (arquitetura de domínio único).
        $response->assertRedirect('https://app.iaqueatende.com.br/onboarding/wizard');

        // AC2a — tenant criado.
        $this->assertDatabaseHas('tenants', [
            'slug' => 'oficina-do-ze',
            'nome' => 'Oficina do Zé',
            'ativo' => true,
        ]);
        $tenant = Tenant::where('slug', 'oficina-do-ze')->first();

        // AC2b — user admin criado, sem re-hash da senha.
        $user = User::withoutGlobalScope('tenant')->where('email', 'jose@oficina.com')->first();
        $this->assertNotNull($user);
        $this->assertSame($tenant->id, $user->tenant_id);
        $this->assertSame('admin', $user->perfil);
        $this->assertTrue((bool) $user->ativo);
        $this->assertTrue(Hash::check('Senha123', $user->password), 'Senha deve continuar válida (sem double-hash)');

        // AC2 — subscription trial criada.
        $this->assertDatabaseHas('subscriptions', [
            'tenant_id' => $tenant->id,
            'plano' => Subscription::PLANO_TRIAL,
            'status' => Subscription::STATUS_TRIAL,
            'gateway' => Subscription::GATEWAY_MANUAL,
        ]);

        // AC2c — signup confirmado e vinculado.
        $signup->refresh();
        $this->assertSame(TenantSignup::STATUS_CONFIRMED, $signup->status);
        $this->assertSame($tenant->id, $signup->tenant_id);

        // AC5 — login passivo.
        $this->assertAuthenticatedAs($user);
    }

    // T8.5 (AC9) — trial_ends_at exatamente 30 dias no futuro.
    public function test_trial_ends_at_definido_para_30_dias(): void
    {
        Carbon::setTestNow('2026-05-28 12:00:00');
        $signup = $this->criarSignupPendente();

        $this->get('/confirmar-email/'.$signup->token_confirmacao);

        $tenant = Tenant::where('slug', 'oficina-do-ze')->first();
        $this->assertSame(
            '2026-06-27 12:00:00',
            $tenant->trial_ends_at->format('Y-m-d H:i:s'),
            'Trial deve ser 30 dias conforme landing page'
        );

        $sub = Subscription::where('tenant_id', $tenant->id)->first();
        $this->assertSame('2026-06-27 12:00:00', $sub->trial_termina_em->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    // T8.3 (AC7) — token expirado mostra view de link expirado.
    public function test_token_expirado_exibe_view_de_reenvio(): void
    {
        $signup = $this->criarSignupPendente([
            'token_expira_em' => now()->subHour(),
        ]);

        $response = $this->get('/confirmar-email/'.$signup->token_confirmacao);

        $response->assertStatus(410);
        $response->assertSee('Link expirado');
        // E-mail mascarado (AC7).
        $response->assertSee('j***@oficina.com');
        $response->assertSee('Reenviar e-mail de confirmação');

        // Nenhum tenant criado.
        $this->assertDatabaseMissing('tenants', ['slug' => 'oficina-do-ze']);
    }

    // T8.4 (AC8) — token já usado redireciona para login sem erro 500.
    public function test_token_ja_confirmado_redireciona_para_login(): void
    {
        $signup = $this->criarSignupPendente([
            'status' => TenantSignup::STATUS_CONFIRMED,
        ]);

        $response = $this->get('/confirmar-email/'.$signup->token_confirmacao);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('info');
    }

    // (AC1) — token inexistente retorna 404 com view de erro.
    public function test_token_inexistente_retorna_404(): void
    {
        $response = $this->get('/confirmar-email/'.(string) Str::uuid());

        $response->assertStatus(404);
        $response->assertSee('Link inválido');
    }

    // T8.2 (AC3) — falha na transação faz rollback completo (sem tenant/user órfão).
    public function test_falha_na_transacao_faz_rollback_completo(): void
    {
        // Cria um signup cujo slug colide com um tenant já existente.
        // A criação do segundo tenant com o mesmo slug viola a unique e
        // dispara exception dentro da transação → rollback.
        Tenant::create([
            'nome' => 'Tenant Existente',
            'slug' => 'oficina-do-ze',
            'ativo' => true,
        ]);

        $signup = $this->criarSignupPendente();

        $response = $this->get('/confirmar-email/'.$signup->token_confirmacao);

        $response->assertStatus(500);
        $response->assertSee('Não foi possível criar sua conta');

        // Nenhum user órfão criado.
        $this->assertDatabaseMissing('users', ['email' => 'jose@oficina.com']);
        // Nenhuma subscription órfã.
        $this->assertSame(0, Subscription::count());
        // Signup permanece pendente (rollback do update também).
        $signup->refresh();
        $this->assertSame(TenantSignup::STATUS_PENDING, $signup->status);
        $this->assertNull($signup->tenant_id);

        // Usuário não está autenticado.
        $this->assertGuest();
    }
}
