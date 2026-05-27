<?php

namespace Tests\Feature;

use App\Models\EntradaEstoque;
use App\Models\Fornecedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\HasTenant;

class DashboardComprasMesTest extends TestCase
{
    use RefreshDatabase, HasTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    // ────────────────────────────────────────────────────────────
    // Widget compras_mes presente no dashboard
    // ────────────────────────────────────────────────────────────

    public function test_dashboard_inclui_campo_compras_mes(): void
    {
        $response = $this->apiAs($this->adminUser)->getJson('/api/dashboard');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'compras_mes' => [
                         'valor_total',
                         'valor_mes_anterior',
                         'variacao_percentual',
                     ],
                 ]);
    }

    // ────────────────────────────────────────────────────────────
    // Valor total correto (soma entradas ativas do mês)
    // ────────────────────────────────────────────────────────────

    public function test_widget_soma_apenas_entradas_ativas_do_mes(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        // 3 entradas ativas no mês atual: 100 + 200 + 300 = 600
        foreach ([100, 200, 300] as $valor) {
            EntradaEstoque::factory()->create([
                'tenant_id'      => $this->tenant->id,
                'fornecedor_id'  => $fornecedor->id,
                'usuario_id'     => $this->adminUser->id,
                'status'         => 'ativa',
                'data_entrada'   => now()->startOfMonth()->addDays(2),
                'valor_total'    => $valor,
                'numero_entrada' => 'ENT-TEST-' . $valor,
            ]);
        }

        $response = $this->apiAs($this->adminUser)->getJson('/api/dashboard');

        $response->assertStatus(200);
        $this->assertEquals(600, $response->json('compras_mes.valor_total'));
    }

    // ────────────────────────────────────────────────────────────
    // Entrada cancelada NÃO é somada
    // ────────────────────────────────────────────────────────────

    public function test_widget_nao_soma_entradas_canceladas(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        // 1 entrada ativa: 500
        EntradaEstoque::factory()->create([
            'tenant_id'      => $this->tenant->id,
            'fornecedor_id'  => $fornecedor->id,
            'usuario_id'     => $this->adminUser->id,
            'status'         => 'ativa',
            'data_entrada'   => now()->startOfMonth()->addDays(1),
            'valor_total'    => 500,
            'numero_entrada' => 'ENT-ATIVA-001',
        ]);

        // 1 entrada cancelada: 999 (não deve ser somada)
        EntradaEstoque::factory()->create([
            'tenant_id'        => $this->tenant->id,
            'fornecedor_id'    => $fornecedor->id,
            'usuario_id'       => $this->adminUser->id,
            'status'           => 'cancelada',
            'data_entrada'     => now()->startOfMonth()->addDays(1),
            'valor_total'      => 999,
            'numero_entrada'   => 'ENT-CANC-001',
            'cancelado_por'    => $this->adminUser->id,
            'cancelado_em'     => now(),
            'cancelado_motivo' => 'Teste cancelamento',
        ]);

        $response = $this->apiAs($this->adminUser)->getJson('/api/dashboard');

        $response->assertStatus(200);
        $this->assertEquals(500, $response->json('compras_mes.valor_total'));
    }

    // ────────────────────────────────────────────────────────────
    // Variação percentual calculada corretamente
    // ────────────────────────────────────────────────────────────

    public function test_variacao_percentual_calculada_corretamente(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        // Mês anterior: 1000
        EntradaEstoque::factory()->create([
            'tenant_id'      => $this->tenant->id,
            'fornecedor_id'  => $fornecedor->id,
            'usuario_id'     => $this->adminUser->id,
            'status'         => 'ativa',
            'data_entrada'   => now()->startOfMonth()->subMonth()->addDays(5),
            'valor_total'    => 1000,
            'numero_entrada' => 'ENT-ANT-001',
        ]);

        // Mês atual: 1500 → variação = +50%
        EntradaEstoque::factory()->create([
            'tenant_id'      => $this->tenant->id,
            'fornecedor_id'  => $fornecedor->id,
            'usuario_id'     => $this->adminUser->id,
            'status'         => 'ativa',
            'data_entrada'   => now()->startOfMonth()->addDays(3),
            'valor_total'    => 1500,
            'numero_entrada' => 'ENT-ATU-001',
        ]);

        $response = $this->apiAs($this->adminUser)->getJson('/api/dashboard');

        $response->assertStatus(200);
        $this->assertEquals(1500, $response->json('compras_mes.valor_total'));
        $this->assertEquals(1000, $response->json('compras_mes.valor_mes_anterior'));
        $this->assertEquals(50.0, $response->json('compras_mes.variacao_percentual'));
    }

    // ────────────────────────────────────────────────────────────
    // Sem mês anterior — variação_percentual é null
    // ────────────────────────────────────────────────────────────

    public function test_variacao_percentual_null_quando_sem_mes_anterior(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        EntradaEstoque::factory()->create([
            'tenant_id'      => $this->tenant->id,
            'fornecedor_id'  => $fornecedor->id,
            'usuario_id'     => $this->adminUser->id,
            'status'         => 'ativa',
            'data_entrada'   => now()->startOfMonth()->addDays(1),
            'valor_total'    => 800,
            'numero_entrada' => 'ENT-SEM-ANT-001',
        ]);

        $response = $this->apiAs($this->adminUser)->getJson('/api/dashboard');

        $response->assertStatus(200)
                 ->assertJsonPath('compras_mes.variacao_percentual', null);
    }
}
