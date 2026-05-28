<?php

namespace Tests\Feature\Signup;

use App\Jobs\SendSignupConfirmationEmailJob;
use App\Models\Tenant;
use App\Models\TenantSignup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PublicSignupTest extends TestCase
{
    use RefreshDatabase;

    private function payloadValido(array $overrides = []): array
    {
        return array_merge([
            'nome_oficina' => 'Oficina do Zé',
            'slug_desejado' => 'oficina-do-ze',
            'cnpj' => null,
            'telefone' => '(81) 99999-0000',
            'cidade' => 'Recife',
            'uf' => 'PE',
            'nome_completo' => 'José da Silva',
            'email' => 'jose@oficina.com',
            'senha' => 'Senha123',
            'senha_confirmation' => 'Senha123',
            'aceite_termos' => '1',
        ], $overrides);
    }

    // T9.1 — happy path
    public function test_cadastro_valido_cria_signup_e_despacha_job(): void
    {
        Queue::fake();

        $response = $this->post('/cadastro', $this->payloadValido());

        $response->assertRedirect(route('cadastro.confirmacao'));

        $this->assertDatabaseHas('tenant_signups', [
            'email' => 'jose@oficina.com',
            'slug_desejado' => 'oficina-do-ze',
            'status' => TenantSignup::STATUS_PENDING,
        ]);

        $signup = TenantSignup::first();
        $this->assertNotNull($signup->token_confirmacao);
        $this->assertNotNull($signup->token_expira_em);
        $this->assertTrue($signup->token_expira_em->isFuture());
        // Senha nunca em texto plano.
        $this->assertNotEquals('Senha123', $signup->senha_hash);

        // Aceites de termos registrados (LGPD).
        $this->assertDatabaseHas('terms_acceptances', [
            'tenant_signup_id' => $signup->id,
            'tipo' => 'termos_uso',
        ]);

        Queue::assertPushed(SendSignupConfirmationEmailJob::class);
    }

    public function test_formulario_renderiza(): void
    {
        $this->get('/cadastro')->assertOk()->assertSee('Criar minha conta');
    }

    // T9.2 — slug reservado
    public function test_slug_reservado_e_rejeitado(): void
    {
        $response = $this->from('/cadastro')
            ->post('/cadastro', $this->payloadValido(['slug_desejado' => 'admin']));

        $response->assertSessionHasErrors('slug_desejado');
        $this->assertDatabaseCount('tenant_signups', 0);
    }

    public function test_verificar_slug_reservado_retorna_indisponivel(): void
    {
        $this->getJson('/cadastro/verificar-slug?slug=admin')
            ->assertOk()
            ->assertJson(['status' => 'indisponivel']);
    }

    public function test_verificar_slug_invalido(): void
    {
        $this->getJson('/cadastro/verificar-slug?slug=ab')
            ->assertOk()
            ->assertJson(['status' => 'invalido']);
    }

    public function test_verificar_slug_disponivel(): void
    {
        $this->getJson('/cadastro/verificar-slug?slug=oficina-nova')
            ->assertOk()
            ->assertJson(['status' => 'disponivel']);
    }

    // T9.3 — email duplicado cross-tenant
    public function test_email_duplicado_em_users_e_rejeitado(): void
    {
        $tenant = Tenant::factory()->create();
        User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'jose@oficina.com',
        ]);

        $this->from('/cadastro')
            ->post('/cadastro', $this->payloadValido())
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('tenant_signups', 0);
    }

    public function test_senha_fraca_e_rejeitada(): void
    {
        $this->from('/cadastro')
            ->post('/cadastro', $this->payloadValido(['senha' => 'minuscula', 'senha_confirmation' => 'minuscula']))
            ->assertSessionHasErrors('senha');
    }

    public function test_termos_obrigatorios(): void
    {
        $payload = $this->payloadValido();
        unset($payload['aceite_termos']);

        $this->from('/cadastro')
            ->post('/cadastro', $payload)
            ->assertSessionHasErrors('aceite_termos');
    }

    // T9.4 — rate limit (5/hora)
    public function test_rate_limit_bloqueia_sexta_submissao(): void
    {
        Queue::fake();

        for ($i = 1; $i <= 5; $i++) {
            $this->post('/cadastro', $this->payloadValido([
                'slug_desejado' => "oficina-{$i}",
                'email' => "user{$i}@oficina.com",
            ]));
        }

        $response = $this->post('/cadastro', $this->payloadValido([
            'slug_desejado' => 'oficina-6',
            'email' => 'user6@oficina.com',
        ]));

        $response->assertStatus(429);
    }
}
