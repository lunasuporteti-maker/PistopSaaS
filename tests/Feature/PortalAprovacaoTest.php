<?php

namespace Tests\Feature;

use App\Jobs\NotificarAprovacaoJob;
use App\Models\Cliente;
use App\Models\Orcamento;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\HasTenant;

/**
 * Story 2.2 — Aprovação online no portal público.
 */
class PortalAprovacaoTest extends TestCase
{
    use RefreshDatabase, HasTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    private function criarOrcamento(string $status = 'orcamento'): Orcamento
    {
        $cliente = Cliente::create([
            'tenant_id' => $this->tenant->id,
            'nome'      => 'Maria Cliente',
            'telefone'  => '84999999999',
        ]);

        $veiculo = Veiculo::create([
            'tenant_id'  => $this->tenant->id,
            'cliente_id' => $cliente->id,
            'marca'      => 'Honda',
            'modelo'     => 'CG 160',
        ]);

        return Orcamento::create([
            'tenant_id'     => $this->tenant->id,
            'cliente_id'    => $cliente->id,
            'veiculo_id'    => $veiculo->id,
            'status'        => $status,
            'valor_total'   => 250.00,
            'token_publico' => Str::uuid()->toString(),
        ]);
    }

    public function test_portal_mostra_botoes_quando_status_orcamento(): void
    {
        $orcamento = $this->criarOrcamento();

        $this->get('/acompanhar/' . $orcamento->token_publico)
            ->assertOk()
            ->assertSee('Aprovar Orçamento')
            ->assertSee('Solicitar Revisão');
    }

    public function test_aprovacao_atualiza_status_e_cria_interacao(): void
    {
        Queue::fake();
        $orcamento = $this->criarOrcamento();

        $this->post('/acompanhar/' . $orcamento->token_publico . '/aprovar', [
            'aceite_termos' => '1',
        ])->assertRedirect('/acompanhar/' . $orcamento->token_publico);

        $this->assertDatabaseHas('orcamentos', [
            'id'                 => $orcamento->id,
            'status'             => 'aprovado',
            'aprovado_por_canal' => 'portal',
        ]);

        $this->assertDatabaseHas('orcamento_interacoes', [
            'orcamento_id' => $orcamento->id,
            'tipo'         => 'aprovacao',
            'usuario_id'   => null,
        ]);

        // OS gerada automaticamente
        $this->assertDatabaseHas('ordens_servico', [
            'orcamento_id' => $orcamento->id,
        ]);

        Queue::assertPushed(NotificarAprovacaoJob::class);
    }

    public function test_aprovacao_sem_aceite_termos_falha_validacao(): void
    {
        $orcamento = $this->criarOrcamento();

        $this->post('/acompanhar/' . $orcamento->token_publico . '/aprovar', [])
            ->assertSessionHasErrors('aceite_termos');

        $this->assertDatabaseHas('orcamentos', [
            'id'     => $orcamento->id,
            'status' => 'orcamento',
        ]);
    }

    public function test_aprovacao_de_orcamento_ja_aprovado_nao_altera_dados(): void
    {
        Queue::fake();
        $orcamento = $this->criarOrcamento('aprovado');

        $this->post('/acompanhar/' . $orcamento->token_publico . '/aprovar', [
            'aceite_termos' => '1',
        ])->assertRedirect('/acompanhar/' . $orcamento->token_publico);

        // Nenhuma interação de aprovação criada
        $this->assertDatabaseMissing('orcamento_interacoes', [
            'orcamento_id' => $orcamento->id,
            'tipo'         => 'aprovacao',
        ]);

        Queue::assertNothingPushed();
    }

    public function test_aprovacao_nao_altera_valor_total(): void
    {
        Queue::fake();
        $orcamento = $this->criarOrcamento();
        $valorAntes = $orcamento->valor_total;

        $this->post('/acompanhar/' . $orcamento->token_publico . '/aprovar', [
            'aceite_termos' => '1',
        ]);

        $this->assertEquals(
            (string) $valorAntes,
            (string) $orcamento->fresh()->valor_total
        );
    }
}
