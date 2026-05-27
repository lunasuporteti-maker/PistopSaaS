<?php

namespace Tests\Feature;

use App\Models\EntradaEstoque;
use App\Models\EntradaEstoqueItem;
use App\Models\Fornecedor;
use App\Models\Peca;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\HasTenant;

class RelatorioComprasTest extends TestCase
{
    use RefreshDatabase, HasTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    // ────────────────────────────────────────────────────────────
    // Relatório retorna campos esperados
    // ────────────────────────────────────────────────────────────

    public function test_relatorio_compras_retorna_estrutura_correta(): void
    {
        $response = $this->apiAs($this->adminUser)->getJson('/api/relatorio/compras');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'periodo'        => ['inicio', 'fim'],
                     'valor_total',
                     'total_entradas',
                     'total_itens',
                     'por_fornecedor',
                 ]);
    }

    // ────────────────────────────────────────────────────────────
    // Valor total e breakdown por fornecedor corretos
    // ────────────────────────────────────────────────────────────

    public function test_relatorio_compras_calcula_valor_e_breakdown_fornecedor(): void
    {
        $fornecedorA = Fornecedor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'nome'      => 'Peças Silva',
        ]);
        $fornecedorB = Fornecedor::factory()->create([
            'tenant_id' => $this->tenant->id,
            'nome'      => 'Auto Lima',
        ]);

        $inicio = now()->startOfMonth()->format('Y-m-d');
        $fim    = now()->format('Y-m-d');

        // Fornecedor A: 2 entradas de 300 e 700 = 1000
        foreach (['ENT-A-001' => 300, 'ENT-A-002' => 700] as $num => $valor) {
            EntradaEstoque::factory()->create([
                'tenant_id'      => $this->tenant->id,
                'fornecedor_id'  => $fornecedorA->id,
                'usuario_id'     => $this->adminUser->id,
                'status'         => 'ativa',
                'data_entrada'   => now()->startOfMonth()->addDays(2),
                'valor_total'    => $valor,
                'numero_entrada' => $num,
            ]);
        }

        // Fornecedor B: 1 entrada de 500
        EntradaEstoque::factory()->create([
            'tenant_id'      => $this->tenant->id,
            'fornecedor_id'  => $fornecedorB->id,
            'usuario_id'     => $this->adminUser->id,
            'status'         => 'ativa',
            'data_entrada'   => now()->startOfMonth()->addDays(3),
            'valor_total'    => 500,
            'numero_entrada' => 'ENT-B-001',
        ]);

        $response = $this->apiAs($this->adminUser)
            ->getJson("/api/relatorio/compras?inicio={$inicio}&fim={$fim}");

        $response->assertStatus(200)
                 ->assertJsonPath('total_entradas', 3);
        $this->assertEquals(1500, $response->json('valor_total'));

        $porFornecedor = collect($response->json('por_fornecedor'));
        $this->assertCount(2, $porFornecedor);

        // Fornecedor A deve aparecer primeiro (maior valor)
        $this->assertEquals($fornecedorA->id, $porFornecedor->first()['fornecedor_id']);
        $this->assertEquals(1000, (float) $porFornecedor->first()['total']);
        $this->assertEquals(2, (int) $porFornecedor->first()['qtd_entradas']);
    }

    // ────────────────────────────────────────────────────────────
    // Filtro por período retorna apenas entradas no intervalo
    // ────────────────────────────────────────────────────────────

    public function test_filtro_periodo_retorna_apenas_entradas_no_intervalo(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        // Dentro do período (2026-01-15)
        EntradaEstoque::factory()->create([
            'tenant_id'      => $this->tenant->id,
            'fornecedor_id'  => $fornecedor->id,
            'usuario_id'     => $this->adminUser->id,
            'status'         => 'ativa',
            'data_entrada'   => '2026-01-15',
            'valor_total'    => 400,
            'numero_entrada' => 'ENT-DENTRO-001',
        ]);

        // Fora do período (2026-02-01)
        EntradaEstoque::factory()->create([
            'tenant_id'      => $this->tenant->id,
            'fornecedor_id'  => $fornecedor->id,
            'usuario_id'     => $this->adminUser->id,
            'status'         => 'ativa',
            'data_entrada'   => '2026-02-01',
            'valor_total'    => 999,
            'numero_entrada' => 'ENT-FORA-001',
        ]);

        $response = $this->apiAs($this->adminUser)
            ->getJson('/api/relatorio/compras?inicio=2026-01-01&fim=2026-01-31');

        $response->assertStatus(200)
                 ->assertJsonPath('total_entradas', 1);
        $this->assertEquals(400, $response->json('valor_total'));
    }

    // ────────────────────────────────────────────────────────────
    // Entradas canceladas não aparecem no relatório
    // ────────────────────────────────────────────────────────────

    public function test_entradas_canceladas_nao_aparecem_no_relatorio(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        EntradaEstoque::factory()->create([
            'tenant_id'        => $this->tenant->id,
            'fornecedor_id'    => $fornecedor->id,
            'usuario_id'       => $this->adminUser->id,
            'status'           => 'cancelada',
            'data_entrada'     => now()->startOfMonth()->addDays(1),
            'valor_total'      => 5000,
            'numero_entrada'   => 'ENT-CANC-REL-001',
            'cancelado_por'    => $this->adminUser->id,
            'cancelado_em'     => now(),
            'cancelado_motivo' => 'Cancelada para teste',
        ]);

        $response = $this->apiAs($this->adminUser)->getJson('/api/relatorio/compras');

        $response->assertStatus(200)
                 ->assertJsonPath('total_entradas', 0);
        $this->assertEquals(0, $response->json('valor_total'));
    }

    // ────────────────────────────────────────────────────────────
    // Permissões
    // ────────────────────────────────────────────────────────────

    public function test_operador_recebe_403_no_relatorio(): void
    {
        $this->apiAs($this->operadorUser)
            ->getJson('/api/relatorio/compras')
            ->assertStatus(403);
    }

    public function test_operador_recebe_403_no_export(): void
    {
        $this->apiAs($this->operadorUser)
            ->getJson('/api/relatorio/compras/exportar')
            ->assertStatus(403);
    }

    // ────────────────────────────────────────────────────────────
    // Export CSV
    // ────────────────────────────────────────────────────────────

    public function test_export_retorna_arquivo_csv(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        EntradaEstoque::factory()->create([
            'tenant_id'      => $this->tenant->id,
            'fornecedor_id'  => $fornecedor->id,
            'usuario_id'     => $this->adminUser->id,
            'status'         => 'ativa',
            'data_entrada'   => now()->startOfMonth()->addDays(1),
            'valor_total'    => 750,
            'numero_entrada' => 'ENT-EXPORT-001',
        ]);

        $response = $this->apiAs($this->adminUser)
            ->getJson('/api/relatorio/compras/exportar');

        $response->assertStatus(200);
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.csv', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('ENT-EXPORT-001', $response->getContent());
    }

    // ────────────────────────────────────────────────────────────
    // Export filtrado por período
    // ────────────────────────────────────────────────────────────

    public function test_export_filtrado_por_periodo(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        EntradaEstoque::factory()->create([
            'tenant_id'      => $this->tenant->id,
            'fornecedor_id'  => $fornecedor->id,
            'usuario_id'     => $this->adminUser->id,
            'status'         => 'ativa',
            'data_entrada'   => '2026-01-10',
            'valor_total'    => 300,
            'numero_entrada' => 'ENT-JAN-001',
        ]);

        EntradaEstoque::factory()->create([
            'tenant_id'      => $this->tenant->id,
            'fornecedor_id'  => $fornecedor->id,
            'usuario_id'     => $this->adminUser->id,
            'status'         => 'ativa',
            'data_entrada'   => '2026-03-10',
            'valor_total'    => 800,
            'numero_entrada' => 'ENT-MAR-001',
        ]);

        $response = $this->apiAs($this->adminUser)
            ->getJson('/api/relatorio/compras/exportar?inicio=2026-01-01&fim=2026-01-31');

        $response->assertStatus(200);
        $conteudo = $response->getContent();
        $this->assertStringContainsString('ENT-JAN-001', $conteudo);
        $this->assertStringNotContainsString('ENT-MAR-001', $conteudo);
    }
}
