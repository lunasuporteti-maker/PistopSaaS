<?php

namespace Tests\Unit\Models;

use App\Models\EntradaEstoque;
use App\Models\EntradaEstoqueItem;
use App\Models\Fornecedor;
use App\Models\Peca;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\HasTenant;

class EntradaEstoqueTest extends TestCase
{
    use RefreshDatabase, HasTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    // ────────────────────────────────────────────────────────────
    // Scopes
    // ────────────────────────────────────────────────────────────

    public function test_scope_ativas_retorna_apenas_ativas(): void
    {
        EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => Fornecedor::factory()->create(['tenant_id' => $this->tenant->id])->id,
            'usuario_id'    => $this->adminUser->id,
            'status'        => 'ativa',
            'numero_entrada' => 'ENT-2026-0001',
        ]);

        EntradaEstoque::factory()->create([
            'tenant_id'       => $this->tenant->id,
            'fornecedor_id'   => Fornecedor::factory()->create(['tenant_id' => $this->tenant->id])->id,
            'usuario_id'      => $this->adminUser->id,
            'status'          => 'cancelada',
            'numero_entrada'  => 'ENT-2026-0002',
            'cancelado_por'   => $this->adminUser->id,
            'cancelado_em'    => now(),
            'cancelado_motivo' => 'Erro',
        ]);

        $ativas = EntradaEstoque::ativas()->get();
        $this->assertCount(1, $ativas);
        $this->assertEquals('ativa', $ativas->first()->status);
    }

    public function test_scope_canceladas_retorna_apenas_canceladas(): void
    {
        EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => Fornecedor::factory()->create(['tenant_id' => $this->tenant->id])->id,
            'usuario_id'    => $this->adminUser->id,
            'status'        => 'ativa',
            'numero_entrada' => 'ENT-2026-0001',
        ]);

        EntradaEstoque::factory()->create([
            'tenant_id'       => $this->tenant->id,
            'fornecedor_id'   => Fornecedor::factory()->create(['tenant_id' => $this->tenant->id])->id,
            'usuario_id'      => $this->adminUser->id,
            'status'          => 'cancelada',
            'numero_entrada'  => 'ENT-2026-0002',
            'cancelado_por'   => $this->adminUser->id,
            'cancelado_em'    => now(),
            'cancelado_motivo' => 'Erro',
        ]);

        $canceladas = EntradaEstoque::canceladas()->get();
        $this->assertCount(1, $canceladas);
        $this->assertEquals('cancelada', $canceladas->first()->status);
    }

    // ────────────────────────────────────────────────────────────
    // isCancelada()
    // ────────────────────────────────────────────────────────────

    public function test_is_cancelada_retorna_true_quando_cancelada(): void
    {
        $entrada = EntradaEstoque::factory()->create([
            'tenant_id'       => $this->tenant->id,
            'fornecedor_id'   => Fornecedor::factory()->create(['tenant_id' => $this->tenant->id])->id,
            'usuario_id'      => $this->adminUser->id,
            'status'          => 'cancelada',
            'numero_entrada'  => 'ENT-2026-0001',
            'cancelado_por'   => $this->adminUser->id,
            'cancelado_em'    => now(),
            'cancelado_motivo' => 'Teste',
        ]);

        $this->assertTrue($entrada->isCancelada());
    }

    public function test_is_cancelada_retorna_false_quando_ativa(): void
    {
        $entrada = EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => Fornecedor::factory()->create(['tenant_id' => $this->tenant->id])->id,
            'usuario_id'    => $this->adminUser->id,
            'status'        => 'ativa',
            'numero_entrada' => 'ENT-2026-0001',
        ]);

        $this->assertFalse($entrada->isCancelada());
    }

    // ────────────────────────────────────────────────────────────
    // Relacionamentos
    // ────────────────────────────────────────────────────────────

    public function test_itens_retorna_colecao_de_entrada_estoque_item(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);
        $entrada    = EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => $fornecedor->id,
            'usuario_id'    => $this->adminUser->id,
            'numero_entrada' => 'ENT-2026-0001',
        ]);

        $peca = Peca::factory()->create(['tenant_id' => $this->tenant->id]);

        EntradaEstoqueItem::factory()->count(2)->create([
            'tenant_id'  => $this->tenant->id,
            'entrada_id' => $entrada->id,
            'peca_id'    => $peca->id,
        ]);

        $this->assertCount(2, $entrada->itens);
        $this->assertInstanceOf(EntradaEstoqueItem::class, $entrada->itens->first());
    }

    public function test_fornecedor_relacionamento_funciona(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);
        $entrada    = EntradaEstoque::factory()->create([
            'tenant_id'     => $this->tenant->id,
            'fornecedor_id' => $fornecedor->id,
            'usuario_id'    => $this->adminUser->id,
            'numero_entrada' => 'ENT-2026-0001',
        ]);

        $this->assertEquals($fornecedor->id, $entrada->fornecedor->id);
        $this->assertEquals($fornecedor->nome, $entrada->fornecedor->nome);
    }
}
