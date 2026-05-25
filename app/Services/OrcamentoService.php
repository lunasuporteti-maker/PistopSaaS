<?php

namespace App\Services;

use App\Models\CatalogoServico;
use App\Models\Lembrete;
use App\Models\Orcamento;
use App\Models\OrdemServico;
use Illuminate\Support\Facades\DB;

class OrcamentoService
{
    /**
     * Gera a OS a partir de um orçamento aprovado ou em serviço.
     *
     * Responsabilidades:
     *   - Criar OrdemServico com número sequencial
     *   - Decrementar estoque das peças usadas
     *   - Copiar peças para os_pecas
     *   - Criar lembretes de revisão automáticos (via CatalogoServico.dias_lembrete)
     *
     * NÃO registra pagamentos — essa é responsabilidade do chamador.
     *
     * @throws \Exception se OS já existir para este orçamento
     */
    public function gerarOs(Orcamento $orcamento): OrdemServico
    {
        if ($orcamento->ordemServico()->exists()) {
            throw new \Exception('OS já foi gerada para este orçamento.');
        }

        $os = null;

        DB::transaction(function () use ($orcamento, &$os) {
            $os = OrdemServico::create([
                'numero_os'     => OrdemServico::gerarNumeroOs(),
                'orcamento_id'  => $orcamento->id,
                'cliente_id'    => $orcamento->cliente_id,
                'veiculo_id'    => $orcamento->veiculo_id,
                'descricao'     => $orcamento->queixa_cliente,
                'valor_total'   => $orcamento->valor_total,
                'finalizado_em' => now(),
            ]);

            // Decrementa estoque e copia peças para a OS
            foreach ($orcamento->pecas()->with('peca')->get() as $item) {
                $item->peca->decrement('quantidade', $item->quantidade);
                $os->pecas()->create([
                    'peca_id'        => $item->peca_id,
                    'quantidade'     => $item->quantidade,
                    'preco_unitario' => $item->preco_unitario,
                ]);
            }

            // Cria lembretes de revisão automáticos
            foreach ($orcamento->servicos as $servico) {
                $catalogo = CatalogoServico::where('nome', 'like', "%{$servico->servico_nome}%")
                    ->whereNotNull('dias_lembrete')
                    ->first();

                if ($catalogo) {
                    Lembrete::create([
                        'cliente_id'    => $orcamento->cliente_id,
                        'veiculo_id'    => $orcamento->veiculo_id,
                        'os_id'         => $os->id,
                        'servico_nome'  => $servico->servico_nome,
                        'data_servico'  => now(),
                        'data_lembrete' => now()->addDays($catalogo->dias_lembrete),
                    ]);
                }
            }
        });

        return $os;
    }
}
