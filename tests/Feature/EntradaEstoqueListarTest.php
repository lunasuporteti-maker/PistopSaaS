<?php

namespace Tests\Feature;

use App\Models\EntradaEstoque;
use App\Models\EntradaEstoqueItem;
use App\Models\Fornecedor;
use App\Models\Peca;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\HasTenant;

class EntradaEstoqueListarTest extends TestCase
{
    use RefreshDatabase, HasTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    // ────────────────────────────────────────────────────────────
    // INDEX — listagem paginada
    // ────────────────────────────────────────────────────────────

    public function test_lista_entradas_paginadas(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        for ($i = 1; $i <= 3; $i++) {
            EntradaEstoque::factory()->create([
                'tenant_id'     => $this->tenant->id,
                'fornecedor_id' => $fornecedor->id,
                'usuario_id'    => $this->adminUser->id,
                'numero_entrada' => "ENT-2026-000{$i}",
            ]);
        }

        $response = $this->apiAs($this->adminUser)
            ->getJson('/api/entradas-estoque');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'current_page', 'per_page', 'total']);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_filtro_por_fornecedor_id(): void
    {
        $f1 = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);
        $f2 = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => $f1->id,
            'usuario_id'    => $this->adminUser->id,
            'numero_entrada' => 'ENT-2026-0001',
        ]);

        EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => $f2->id,
            'usuario_id'    => $this->adminUser->id,
            'numero_entrada' => 'ENT-2026-0002',
        ]);

        $response = $this->apiAs($this->adminUser)
            ->getJson("/api/entradas-estoque?fornecedor_id={$f1->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($f1->id, $response->json('data.0.fornecedor_id'));
    }

    public function test_filtro_por_status_cancelada(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => $fornecedor->id,
            'usuario_id'    => $this->adminUser->id,
            'status'        => 'ativa',
            'numero_entrada' => 'ENT-2026-0001',
        ]);

        EntradaEstoque::factory()->create([
            'tenant_id'       => $this->tenant->id,
            'fornecedor_id'   => $fornecedor->id,
            'usuario_id'      => $this->adminUser->id,
            'status'          => 'cancelada',
            'numero_entrada'  => 'ENT-2026-0002',
            'cancelado_por'   => $this->adminUser->id,
            'cancelado_em'    => now(),
            'cancelado_motivo' => 'Erro',
        ]);

        $response = $this->apiAs($this->adminUser)
            ->getJson('/api/entradas-estoque?status=cancelada');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('cancelada', $response->json('data.0.status'));
    }

    public function test_filtro_por_peca_id(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);
        $peca1      = Peca::factory()->create(['tenant_id' => $this->tenant->id]);
        $peca2      = Peca::factory()->create(['tenant_id' => $this->tenant->id]);

        $e1 = EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => $fornecedor->id,
            'usuario_id'    => $this->adminUser->id,
            'numero_entrada' => 'ENT-2026-0001',
        ]);

        $e2 = EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => $fornecedor->id,
            'usuario_id'    => $this->adminUser->id,
            'numero_entrada' => 'ENT-2026-0002',
        ]);

        // e1 tem peca1; e2 tem peca2
        EntradaEstoqueItem::factory()->create([
            'tenant_id'  => $this->tenant->id,
            'entrada_id' => $e1->id,
            'peca_id'    => $peca1->id,
        ]);

        EntradaEstoqueItem::factory()->create([
            'tenant_id'  => $this->tenant->id,
            'entrada_id' => $e2->id,
            'peca_id'    => $peca2->id,
        ]);

        $response = $this->apiAs($this->adminUser)
            ->getJson("/api/entradas-estoque?peca_id={$peca1->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($e1->id, $response->json('data.0.id'));
    }

    // ────────────────────────────────────────────────────────────
    // SHOW
    // ────────────────────────────────────────────────────────────

    public function test_show_retorna_entrada_com_itens_e_fornecedor(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);
        $entrada    = EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => $fornecedor->id,
            'usuario_id'    => $this->adminUser->id,
            'numero_entrada' => 'ENT-2026-0001',
        ]);

        $peca = Peca::factory()->create(['tenant_id' => $this->tenant->id]);
        EntradaEstoqueItem::factory()->create([
            'tenant_id'  => $this->tenant->id,
            'entrada_id' => $entrada->id,
            'peca_id'    => $peca->id,
        ]);

        $response = $this->apiAs($this->adminUser)
            ->getJson("/api/entradas-estoque/{$entrada->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['numero_entrada' => 'ENT-2026-0001']);

        // Itens aninhados
        $this->assertCount(1, $response->json('itens'));
        $this->assertArrayHasKey('fornecedor', $response->json());
    }

    // ────────────────────────────────────────────────────────────
    // Histórico de compras por Fornecedor
    // ────────────────────────────────────────────────────────────

    public function test_historico_compras_por_fornecedor(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        // Criar 2 entradas com números únicos
        foreach (['ENT-2026-0001', 'ENT-2026-0002'] as $num) {
            EntradaEstoque::factory()->create([
                'tenant_id'     => $this->tenant->id,
                'fornecedor_id' => $fornecedor->id,
                'usuario_id'    => $this->adminUser->id,
                'numero_entrada' => $num,
            ]);
        }

        $response = $this->apiAs($this->adminUser)
            ->getJson("/api/fornecedores/{$fornecedor->id}/historico-compras");

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'total']);

        // Apenas os campos selecionados
        $item = $response->json('data.0');
        $this->assertArrayHasKey('numero_entrada', $item);
        $this->assertArrayHasKey('valor_total', $item);
        $this->assertArrayHasKey('status', $item);
    }

    // ────────────────────────────────────────────────────────────
    // Histórico de compras por Peça
    // ────────────────────────────────────────────────────────────

    public function test_historico_compras_por_peca(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);
        $peca       = Peca::factory()->create(['tenant_id' => $this->tenant->id]);

        $entrada = EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => $fornecedor->id,
            'usuario_id'    => $this->adminUser->id,
            'numero_entrada' => 'ENT-2026-0001',
        ]);

        EntradaEstoqueItem::factory()->create([
            'tenant_id'            => $this->tenant->id,
            'entrada_id'           => $entrada->id,
            'peca_id'              => $peca->id,
            'quantidade'           => 10,
            'preco_custo_unitario' => 15.00,
            'subtotal'             => 150.00,
        ]);

        $response = $this->apiAs($this->adminUser)
            ->getJson("/api/pecas/{$peca->id}/historico-compras");

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'total']);

        $item = $response->json('data.0');
        $this->assertArrayHasKey('quantidade', $item);
        $this->assertArrayHasKey('preco_custo_unitario', $item);
        $this->assertEquals('15.00', $item['preco_custo_unitario']);
    }

    // ────────────────────────────────────────────────────────────
    // Export CSV
    // ────────────────────────────────────────────────────────────

    public function test_export_gera_csv(): void
    {
        $fornecedor = Fornecedor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'nome'      => 'Auto Peças Silva',
        ]);

        EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => $fornecedor->id,
            'usuario_id'    => $this->adminUser->id,
            'numero_entrada' => 'ENT-2026-0001',
            'status'        => 'ativa',
        ]);

        $response = $this->apiAs($this->adminUser)
            ->get('/api/entradas-estoque/exportar');

        $response->assertStatus(200);
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('ENT-2026-0001', $response->getContent());
    }

    // ────────────────────────────────────────────────────────────
    // Permissões
    // ────────────────────────────────────────────────────────────

    public function test_operador_nao_pode_listar_entradas(): void
    {
        $this->apiAs($this->operadorUser)
            ->getJson('/api/entradas-estoque')
            ->assertStatus(403);
    }
}
