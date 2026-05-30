<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Services\AsaasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Tests\Traits\HasTenant;

/**
 * Stories 6.2 / 6.3 / 6.4 — página /assinatura: status estendido, pendentes,
 * histórico, fallback legado e degradação graciosa.
 */
class AssinaturaPageTest extends TestCase
{
    use HasTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    private function comCustomerId(string $customerId = 'cus_pagina123'): void
    {
        Subscription::factory()->create([
            'tenant_id' => $this->tenant->id,
            'gateway_customer_id' => $customerId,
        ]);
    }

    /** Mocka o AsaasService no container */
    private function mockAsaas(?array $pendentes, ?array $historico = []): void
    {
        $mock = Mockery::mock(AsaasService::class);
        $mock->shouldReceive('pagamentosPendentes')->andReturn($pendentes);
        $mock->shouldReceive('listarPagamentos')->andReturn($historico);
        $this->app->instance(AsaasService::class, $mock);
    }

    private function abrirPagina()
    {
        return $this->actingAs($this->adminUser)->get('/assinatura');
    }

    // ── Story 6.2 ─────────────────────────────────────────────────────────────

    public function test_tenant_com_pendentes_exibe_bloco_e_link_pagar(): void
    {
        $this->tenant->update(['plano_ativo' => true, 'plano_vence_em' => now()->addDays(10)]);
        $this->comCustomerId();
        $this->mockAsaas([
            ['status' => 'PENDING', 'value' => 99.90, 'dueDate' => '2026-06-10', 'invoiceUrl' => 'https://asaas.com/i/abc'],
        ]);

        $this->abrirPagina()
            ->assertOk()
            ->assertSee('Pagamento pendente')
            ->assertSee('Pagar agora')
            ->assertSee('https://asaas.com/i/abc')
            ->assertSee('99,90');
    }

    public function test_tenant_sem_pendentes_nao_exibe_bloco(): void
    {
        $this->tenant->update(['plano_ativo' => true, 'plano_vence_em' => now()->addDays(10)]);
        $this->comCustomerId();
        $this->mockAsaas([]);

        $this->abrirPagina()
            ->assertOk()
            ->assertDontSee('Pagamento pendente');
    }

    public function test_bank_slip_url_exibe_link_baixar_boleto(): void
    {
        $this->tenant->update(['plano_ativo' => true, 'plano_vence_em' => now()->addDays(10)]);
        $this->comCustomerId();
        $this->mockAsaas([
            ['status' => 'OVERDUE', 'value' => 157.50, 'dueDate' => '2026-05-01', 'invoiceUrl' => 'https://asaas.com/i/x', 'bankSlipUrl' => 'https://asaas.com/b/x.pdf'],
        ]);

        $this->abrirPagina()
            ->assertOk()
            ->assertSee('Baixar boleto')
            ->assertSee('https://asaas.com/b/x.pdf')
            ->assertSee('Vencida');
    }

    public function test_card_estendido_exibe_validade_e_dias_restantes(): void
    {
        $this->tenant->update(['plano_ativo' => true, 'plano_vence_em' => now()->addDays(20)]);
        $this->comCustomerId();
        $this->mockAsaas([]);

        $this->abrirPagina()
            ->assertOk()
            ->assertSee('Plano Pro')
            ->assertSee('dia(s)');
    }

    public function test_tenant_em_trial_preserva_bloco_de_uso(): void
    {
        $this->tenant->update(['trial_ends_at' => now()->addDays(3), 'plano_ativo' => false]);
        // Sem subscription → sem chamadas Asaas
        $this->mockAsaas([]);

        $this->abrirPagina()
            ->assertOk()
            ->assertSee('Uso do Trial');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
