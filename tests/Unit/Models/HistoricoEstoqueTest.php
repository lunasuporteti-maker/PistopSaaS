<?php

namespace Tests\Unit\Models;

use App\Models\HistoricoEstoque;
use App\Models\Peca;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Tests\Traits\HasTenant;

class HistoricoEstoqueTest extends TestCase
{
    use RefreshDatabase, HasTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_tabela_historico_nao_tem_updated_at(): void
    {
        $colunas = Schema::getColumnListing('historico_estoque');

        $this->assertContains('created_at', $colunas);
        $this->assertNotContains('updated_at', $colunas);
    }

    public function test_historico_pode_ser_criado_com_factory(): void
    {
        $peca = Peca::factory()->create(['tenant_id' => $this->tenant->id]);

        $historico = HistoricoEstoque::factory()->create([
            'tenant_id'         => $this->tenant->id,
            'peca_id'           => $peca->id,
            'tipo'              => 'entrada',
            'quantidade_antes'  => 10,
            'quantidade_depois' => 15,
            'quantidade_delta'  => 5,
            'referencia_tipo'   => 'entrada_estoque',
            'referencia_id'     => 1,
        ]);

        $this->assertDatabaseHas('historico_estoque', [
            'id'              => $historico->id,
            'tipo'            => 'entrada',
            'quantidade_delta' => 5,
        ]);

        $this->assertNotNull($historico->created_at);
    }

    public function test_historico_nao_tem_timestamps_automaticos(): void
    {
        $peca = Peca::factory()->create(['tenant_id' => $this->tenant->id]);

        $historico = new HistoricoEstoque([
            'tenant_id'         => $this->tenant->id,
            'peca_id'           => $peca->id,
            'tipo'              => 'ajuste',
            'quantidade_antes'  => 5,
            'quantidade_depois' => 8,
            'quantidade_delta'  => 3,
            'referencia_tipo'   => 'ajuste_manual',
            'referencia_id'     => 1,
        ]);

        // $timestamps = false — não gera updated_at
        $this->assertFalse($historico->timestamps);
    }

    public function test_peca_relacionamento_funciona(): void
    {
        $peca = Peca::factory()->create(['tenant_id' => $this->tenant->id]);

        $historico = HistoricoEstoque::factory()->create([
            'tenant_id'         => $this->tenant->id,
            'peca_id'           => $peca->id,
            'referencia_tipo'   => 'ajuste_manual',
            'referencia_id'     => 1,
        ]);

        $this->assertEquals($peca->id, $historico->peca->id);
    }
}
