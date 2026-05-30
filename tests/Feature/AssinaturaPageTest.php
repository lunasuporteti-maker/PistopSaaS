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

    // ── Story 6.3 ─────────────────────────────────────────────────────────────

    public function test_historico_exibe_tabela_com_cobrancas(): void
    {
        $this->tenant->update(['plano_ativo' => true, 'plano_vence_em' => now()->addDays(10)]);
        $this->comCustomerId();
        $this->mockAsaas([], [
            ['status' => 'RECEIVED', 'value' => 99.90, 'dueDate' => '2026-04-10', 'paymentDate' => '2026-04-09', 'description' => 'PitStop Plano Pro', 'transactionReceiptUrl' => 'https://asaas.com/r/1'],
            ['status' => 'OVERDUE', 'value' => 99.90, 'dueDate' => '2026-05-10', 'description' => 'PitStop Plano Pro', 'invoiceUrl' => 'https://asaas.com/i/2'],
        ]);

        $this->abrirPagina()
            ->assertOk()
            ->assertSee('Histórico de pagamentos')
            ->assertSee('PitStop Plano Pro');
    }

    public function test_cobranca_paga_exibe_badge_verde_e_ver_recibo(): void
    {
        $this->tenant->update(['plano_ativo' => true, 'plano_vence_em' => now()->addDays(10)]);
        $this->comCustomerId();
        $this->mockAsaas([], [
            ['status' => 'CONFIRMED', 'value' => 157.50, 'dueDate' => '2026-04-10', 'paymentDate' => '2026-04-10', 'description' => 'Pro Max', 'transactionReceiptUrl' => 'https://asaas.com/r/abc'],
        ]);

        $this->abrirPagina()
            ->assertOk()
            ->assertSee('badge-success', false)
            ->assertSee('Pago')
            ->assertSee('Ver recibo')
            ->assertSee('https://asaas.com/r/abc');
    }

    public function test_cobranca_overdue_exibe_badge_vermelho_e_pagar(): void
    {
        $this->tenant->update(['plano_ativo' => true, 'plano_vence_em' => now()->addDays(10)]);
        $this->comCustomerId();
        $this->mockAsaas([], [
            ['status' => 'OVERDUE', 'value' => 99.90, 'dueDate' => '2026-05-01', 'description' => 'Pro', 'invoiceUrl' => 'https://asaas.com/i/over'],
        ]);

        $this->abrirPagina()
            ->assertOk()
            ->assertSee('badge-danger', false)
            ->assertSee('Atrasado')
            ->assertSee('https://asaas.com/i/over');
    }

    public function test_status_desconhecido_exibe_em_processamento(): void
    {
        $this->tenant->update(['plano_ativo' => true, 'plano_vence_em' => now()->addDays(10)]);
        $this->comCustomerId();
        $this->mockAsaas([], [
            ['status' => 'AWAITING_RISK_ANALYSIS', 'value' => 99.90, 'dueDate' => '2026-05-20', 'description' => 'Pro'],
        ]);

        $this->abrirPagina()
            ->assertOk()
            ->assertSee('Em processamento')
            ->assertSee('badge-light', false);
    }

    public function test_subscription_log_presente_e_colapsado_por_padrao(): void
    {
        $this->tenant->update(['plano_ativo' => true, 'plano_vence_em' => now()->addDays(10)]);
        $this->comCustomerId();
        $this->mockAsaas([], []);

        $this->abrirPagina()
            ->assertOk()
            ->assertSee('Histórico de eventos do sistema')
            ->assertSee('collapsed-card', false);
    }

    public function test_sem_customer_id_tabela_historico_ausente_log_presente(): void
    {
        $this->tenant->update(['plano_ativo' => true, 'plano_vence_em' => now()->addDays(10)]);
        // sem subscription / customer_id — não mocka (não deve chamar Asaas)
        $this->mockAsaas([], []);

        $this->abrirPagina()
            ->assertOk()
            ->assertDontSee('Histórico de pagamentos')
            ->assertSee('Histórico de eventos do sistema');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
