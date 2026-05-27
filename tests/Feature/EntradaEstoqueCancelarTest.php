<?php

namespace Tests\Feature;

use App\Models\EntradaEstoque;
use App\Models\EntradaEstoqueItem;
use App\Models\Fornecedor;
use App\Models\HistoricoEstoque;
use App\Models\Peca;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\HasTenant;

class EntradaEstoqueCancelarTest extends TestCase
{
    use RefreshDatabase, HasTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    // ────────────────────────────────────────────────────────────
    // Cancelamento bem-sucedido
    // ────────────────────────────────────────────────────────────

    public function test_cancelamento_reverte_estoque_e_registra_historico(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);
        $peca       = Peca::factory()->create(['tenant_id' => $this->tenant->id, 'quantidade' => 20]);

        $entrada = EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => $fornecedor->id,
            'usuario_id'    => $this->adminUser->id,
            'status'        => 'ativa',
            'numero_entrada' => 'ENT-2026-0001',
        ]);

        EntradaEstoqueItem::factory()->create([
            'tenant_id'  => $this->tenant->id,
            'entrada_id' => $entrada->id,
            'peca_id'    => $peca->id,
            'quantidade' => 10,
        ]);

        $response = $this->apiAs($this->adminUser)
            ->postJson("/api/entradas-estoque/{$entrada->id}/cancelar", [
                'motivo' => 'Erro na nota fiscal digitada errado',
            ]);

        $response->assertStatus(200);

        // Entrada marcada como cancelada
        $this->assertDatabaseHas('entradas_estoque', [
            'id'     => $entrada->id,
            'status' => 'cancelada',
        ]);
        $this->assertNotNull($response->json('cancelado_em'));
        $this->assertNotNull($response->json('cancelado_motivo'));

        // Estoque decrementado de volta: 20 - 10 = 10
        $this->assertDatabaseHas('pecas', ['id' => $peca->id, 'quantidade' => 10]);

        // Historico registrado com tipo cancelamento
        $this->assertDatabaseHas('historico_estoque', [
            'peca_id'          => $peca->id,
            'tipo'             => 'cancelamento',
            'quantidade_antes' => 20,
            'quantidade_depois' => 10,
            'quantidade_delta' => -10,
            'referencia_tipo'  => 'entrada_estoque',
            'referencia_id'    => $entrada->id,
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // Bloqueio anti-negativo
    // ────────────────────────────────────────────────────────────

    public function test_cancelamento_bloqueado_por_estoque_insuficiente(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        // Peça com quantidade atual MENOR que a qtd da entrada
        $peca = Peca::factory()->create(['tenant_id' => $this->tenant->id, 'quantidade' => 2]);

        $entrada = EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => $fornecedor->id,
            'usuario_id'    => $this->adminUser->id,
            'status'        => 'ativa',
            'numero_entrada' => 'ENT-2026-0001',
        ]);

        EntradaEstoqueItem::factory()->create([
            'tenant_id'  => $this->tenant->id,
            'entrada_id' => $entrada->id,
            'peca_id'    => $peca->id,
            'quantidade' => 5, // tentativa de decrementar 5, mas só há 2
        ]);

        $response = $this->apiAs($this->adminUser)
            ->postJson("/api/entradas-estoque/{$entrada->id}/cancelar", [
                'motivo' => 'Cancelamento de teste que deve falhar aqui',
            ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['estoque']);

        // Estoque NÃO alterado (rollback)
        $this->assertDatabaseHas('pecas', ['id' => $peca->id, 'quantidade' => 2]);
        $this->assertDatabaseCount('historico_estoque', 0);

        // Entrada ainda ativa
        $this->assertDatabaseHas('entradas_estoque', ['id' => $entrada->id, 'status' => 'ativa']);
    }

    // ────────────────────────────────────────────────────────────
    // Cancelar entrada já cancelada
    // ────────────────────────────────────────────────────────────

    public function test_cancelar_entrada_ja_cancelada_retorna_422(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        $entrada = EntradaEstoque::factory()->create([
            'tenant_id'       => $this->tenant->id,
            'fornecedor_id'   => $fornecedor->id,
            'usuario_id'      => $this->adminUser->id,
            'status'          => 'cancelada',
            'numero_entrada'  => 'ENT-2026-0001',
            'cancelado_por'   => $this->adminUser->id,
            'cancelado_em'    => now(),
            'cancelado_motivo' => 'Já cancelada anteriormente',
        ]);

        $this->apiAs($this->adminUser)
            ->postJson("/api/entradas-estoque/{$entrada->id}/cancelar", [
                'motivo' => 'Tentar cancelar novamente não pode',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // ────────────────────────────────────────────────────────────
    // Validação de motivo
    // ────────────────────────────────────────────────────────────

    public function test_cancelar_sem_motivo_retorna_422(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        $entrada = EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => $fornecedor->id,
            'usuario_id'    => $this->adminUser->id,
            'status'        => 'ativa',
            'numero_entrada' => 'ENT-2026-0001',
        ]);

        $this->apiAs($this->adminUser)
            ->postJson("/api/entradas-estoque/{$entrada->id}/cancelar", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['motivo']);
    }

    // ────────────────────────────────────────────────────────────
    // Permissões
    // ────────────────────────────────────────────────────────────

    public function test_operador_nao_pode_cancelar_entrada(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        $entrada = EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => $fornecedor->id,
            'usuario_id'    => $this->adminUser->id,
            'status'        => 'ativa',
            'numero_entrada' => 'ENT-2026-0001',
        ]);

        $this->apiAs($this->operadorUser)
            ->postJson("/api/entradas-estoque/{$entrada->id}/cancelar", [
                'motivo' => 'Operador tentando cancelar sem permissão',
            ])
            ->assertStatus(403);
    }

    // ────────────────────────────────────────────────────────────
    // Histórico de movimentações
    // ────────────────────────────────────────────────────────────

    public function test_historico_movimentacoes_exibe_tipos_corretos(): void
    {
        $peca = Peca::factory()->create(['tenant_id' => $this->tenant->id]);

        HistoricoEstoque::factory()->create([
            'tenant_id'        => $this->tenant->id,
            'peca_id'          => $peca->id,
            'tipo'             => 'entrada',
            'quantidade_antes' => 0,
            'quantidade_depois' => 10,
            'quantidade_delta' => 10,
            'referencia_tipo'  => 'entrada_estoque',
            'referencia_id'    => 1,
        ]);

        HistoricoEstoque::factory()->create([
            'tenant_id'        => $this->tenant->id,
            'peca_id'          => $peca->id,
            'tipo'             => 'cancelamento',
            'quantidade_antes' => 10,
            'quantidade_depois' => 0,
            'quantidade_delta' => -10,
            'referencia_tipo'  => 'entrada_estoque',
            'referencia_id'    => 1,
        ]);

        $response = $this->apiAs($this->adminUser)
            ->getJson("/api/pecas/{$peca->id}/historico-estoque");

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'total']);

        $tipos = collect($response->json('data'))->pluck('tipo');
        $this->assertContains('entrada', $tipos->toArray());
        $this->assertContains('cancelamento', $tipos->toArray());
    }

    public function test_operador_pode_ver_historico_movimentacoes(): void
    {
        $peca = Peca::factory()->create(['tenant_id' => $this->tenant->id]);

        HistoricoEstoque::factory()->create([
            'tenant_id'       => $this->tenant->id,
            'peca_id'         => $peca->id,
            'referencia_tipo' => 'ajuste_manual',
            'referencia_id'   => 1,
        ]);

        $this->apiAs($this->operadorUser)
            ->getJson("/api/pecas/{$peca->id}/historico-estoque")
            ->assertStatus(200);
    }
}
