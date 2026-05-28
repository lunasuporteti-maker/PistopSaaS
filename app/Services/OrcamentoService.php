<?php

namespace App\Services;

use App\Models\CatalogoServico;
use App\Models\HistoricoEstoque;
use App\Models\Lembrete;
use App\Models\Orcamento;
use App\Models\OrdemServico;
use Illuminate\Support\Facades\DB;

class OrcamentoService
{
    /**
     * Gera a OS a partir de um orçamento aprovado.
     * Se OS já existir, retorna a existente sem reprocessar (idempotente).
     */
    public function gerarOs(Orcamento $orcamento): OrdemServico
    {
        $existente = $orcamento->ordemServico()->first();
        if ($existente) {
            return $existente;
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
                'status'        => 'aprovado',
                'token_publico' => $orcamento->token_publico ?? \Illuminate\Support\Str::random(48),
                'aprovado_em'   => now(),
                'finalizado_em' => null,
            ]);

            // Decrementa estoque e copia peças para a OS
            foreach ($orcamento->pecas()->with('peca')->get() as $item) {
                $qtdAntes = $item->peca->quantidade;
                $item->peca->decrement('quantidade', $item->quantidade);

                // Registrar saída no histórico de estoque
                $this->registrarHistoricoSaida(
                    $item->peca,
                    $qtdAntes,
                    (int) $item->quantidade,
                    'ordem_servico',
                    $os->id,
                );

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

    /**
     * Registra saída de estoque no historico_estoque.
     * Método privado — não lança exceção para não quebrar fluxo existente.
     */
    private function registrarHistoricoSaida(
        \App\Models\Peca $peca,
        int $qtdAntes,
        int $quantidade,
        string $referenciaTipo,
        int $referenciaId,
    ): void {
        try {
            if (DB::getSchemaBuilder()->hasTable('historico_estoque')) {
                HistoricoEstoque::create([
                    'tenant_id'         => $peca->tenant_id,
                    'peca_id'           => $peca->id,
                    'tipo'              => 'saida',
                    'quantidade_antes'  => $qtdAntes,
                    'quantidade_depois' => $qtdAntes - $quantidade,
                    'quantidade_delta'  => -$quantidade,
                    'referencia_tipo'   => $referenciaTipo,
                    'referencia_id'     => $referenciaId,
                    'usuario_id'        => auth()->id(),
                    'created_at'        => now(),
                ]);
            }
        } catch (\Throwable $e) {
            logger()->warning("OrcamentoService: historico_estoque falhou — {$e->getMessage()}");
        }
    }
}
