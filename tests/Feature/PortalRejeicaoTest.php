<?php

namespace Tests\Feature;

use App\Jobs\NotificarRejeicaoJob;
use App\Models\Cliente;
use App\Models\Orcamento;
use App\Models\OrcamentoInteracao;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\HasTenant;

/**
 * Story 2.3 — Rejeição / solicitação de revisão no portal público.
 */
class PortalRejeicaoTest extends TestCase
{
    use RefreshDatabase, HasTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    private function criarOrcamento(): Orcamento
    {
        $cliente = Cliente::create([
            'tenant_id' => $this->tenant->id,
            'nome'      => 'João Cliente',
            'telefone'  => '84988888888',
        ]);

        $veiculo = Veiculo::create([
            'tenant_id'  => $this->tenant->id,
            'cliente_id' => $cliente->id,
            'marca'      => 'Yamaha',
            'modelo'     => 'Factor 150',
        ]);

        return Orcamento::create([
            'tenant_id'     => $this->tenant->id,
            'cliente_id'    => $cliente->id,
            'veiculo_id'    => $veiculo->id,
            'status'        => 'orcamento',
            'valor_total'   => 400.00,
            'token_publico' => Str::uuid()->toString(),
        ]);
    }

    public function test_rejeicao_nao_altera_status_do_orcamento(): void
    {
        Queue::fake();
        $orcamento = $this->criarOrcamento();

        $this->post('/acompanhar/' . $orcamento->token_publico . '/rejeitar', [
            'motivo' => 'Gostaria de entender melhor o valor da peça.',
        ])->assertRedirect('/acompanhar/' . $orcamento->token_publico);

        // Status permanece 'orcamento' — NÃO vira cancelado
        $this->assertDatabaseHas('orcamentos', [
            'id'     => $orcamento->id,
            'status' => 'orcamento',
        ]);
        $this->assertDatabaseMissing('orcamentos', [
            'id'     => $orcamento->id,
            'status' => 'cancelado',
        ]);

        Queue::assertPushed(NotificarRejeicaoJob::class);
    }

    public function test_rejeicao_cria_interacao_tipo_rejeicao(): void
    {
        Queue::fake();
        $orcamento = $this->criarOrcamento();

        $this->post('/acompanhar/' . $orcamento->token_publico . '/rejeitar', [
            'motivo' => 'O prazo informado está muito longo para mim.',
        ]);

        $this->assertDatabaseHas('orcamento_interacoes', [
            'orcamento_id' => $orcamento->id,
            'tipo'         => 'rejeicao',
            'usuario_id'   => null,
        ]);
    }

    public function test_motivo_curto_retorna_erro_de_validacao(): void
    {
        $orcamento = $this->criarOrcamento();

        $this->post('/acompanhar/' . $orcamento->token_publico . '/rejeitar', [
            'motivo' => 'curto',
        ])->assertSessionHasErrors('motivo');

        $this->assertDatabaseMissing('orcamento_interacoes', [
            'orcamento_id' => $orcamento->id,
            'tipo'         => 'rejeicao',
        ]);
    }

    public function test_motivo_com_html_e_sanitizado(): void
    {
        Queue::fake();
        $orcamento = $this->criarOrcamento();

        $this->post('/acompanhar/' . $orcamento->token_publico . '/rejeitar', [
            'motivo' => '<script>alert(1)</script>O preço está acima do esperado.',
        ]);

        $interacao = OrcamentoInteracao::where('orcamento_id', $orcamento->id)
            ->where('tipo', 'rejeicao')
            ->first();

        $this->assertNotNull($interacao);
        $motivoSalvo = $interacao->dados_json['motivo'];

        // strip_tags removeu o script — texto puro
        $this->assertStringNotContainsString('<script>', $motivoSalvo);
        $this->assertStringContainsString('O preço está acima do esperado.', $motivoSalvo);
    }
}
