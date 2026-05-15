<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Orcamento;
use App\Models\OrdemServico;
use App\Models\Lembrete;
use App\Models\CatalogoServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrcamentoController extends Controller
{
    public function index(Request $request)
    {
        $query = Orcamento::with(['cliente', 'veiculo']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'     => 'required|exists:clientes,id',
            'veiculo_id'     => 'required|exists:veiculos,id',
            'observacao'     => 'nullable|string',
            'km_entrada'     => 'nullable|integer',
            'queixa_cliente' => 'nullable|string',
            'servicos'       => 'nullable|array',
            'servicos.*.servico_nome' => 'required_with:servicos|string',
            'servicos.*.valor'        => 'required_with:servicos|numeric|min:0',
            'pecas'          => 'nullable|array',
            'pecas.*.peca_id'         => 'required_with:pecas|exists:pecas,id',
            'pecas.*.quantidade'      => 'required_with:pecas|integer|min:1',
            'pecas.*.preco_unitario'  => 'required_with:pecas|numeric|min:0',
            'mao_de_obra'    => 'nullable|array',
            'mao_de_obra.*.mao_de_obra_id' => 'nullable|exists:mao_de_obra,id',
            'mao_de_obra.*.nome_custom'    => 'nullable|string',
            'mao_de_obra.*.valor'          => 'required_with:mao_de_obra|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $orcamento = Orcamento::create([
                'cliente_id'     => $data['cliente_id'],
                'veiculo_id'     => $data['veiculo_id'],
                'observacao'     => $data['observacao'] ?? null,
                'km_entrada'     => $data['km_entrada'] ?? null,
                'queixa_cliente' => $data['queixa_cliente'] ?? null,
                'status'         => 'orcamento',
                'valor_total'    => 0,
            ]);

            $total = 0;

            foreach ($data['servicos'] ?? [] as $item) {
                $orcamento->servicos()->create($item);
                $total += $item['valor'];
            }

            foreach ($data['pecas'] ?? [] as $item) {
                $orcamento->pecas()->create($item);
                $total += $item['quantidade'] * $item['preco_unitario'];
            }

            foreach ($data['mao_de_obra'] ?? [] as $item) {
                $orcamento->maoDeObra()->create($item);
                $total += $item['valor'];
            }

            $orcamento->update(['valor_total' => $total]);

            DB::commit();
            return response()->json($orcamento->load(['servicos', 'pecas.peca', 'maoDeObra']), 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao criar orçamento.'], 500);
        }
    }

    public function show(Orcamento $orcamento)
    {
        return response()->json($orcamento->load(['cliente', 'veiculo']));
    }

    public function detalhes(Orcamento $orcamento)
    {
        return response()->json($orcamento->load([
            'cliente', 'veiculo',
            'servicos', 'pecas.peca', 'maoDeObra.maoDeObra',
            'ordemServico',
        ]));
    }

    public function update(Request $request, Orcamento $orcamento)
    {
        $data = $request->validate([
            'observacao'      => 'nullable|string',
            'km_entrada'      => 'nullable|integer',
            'queixa_cliente'  => 'nullable|string',
            'parecer_tecnico' => 'nullable|string',
            'servicos'        => 'nullable|array',
            'pecas'           => 'nullable|array',
            'mao_de_obra'     => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $orcamento->update([
                'observacao'      => $data['observacao'] ?? $orcamento->observacao,
                'km_entrada'      => $data['km_entrada'] ?? $orcamento->km_entrada,
                'queixa_cliente'  => $data['queixa_cliente'] ?? $orcamento->queixa_cliente,
                'parecer_tecnico' => $data['parecer_tecnico'] ?? $orcamento->parecer_tecnico,
            ]);

            if (array_key_exists('servicos', $data) || array_key_exists('pecas', $data) || array_key_exists('mao_de_obra', $data)) {
                $total = 0;

                if (array_key_exists('servicos', $data)) {
                    $orcamento->servicos()->delete();
                    foreach ($data['servicos'] ?? [] as $item) {
                        $orcamento->servicos()->create($item);
                        $total += $item['valor'];
                    }
                } else {
                    $total += $orcamento->servicos->sum('valor');
                }

                if (array_key_exists('pecas', $data)) {
                    $orcamento->pecas()->delete();
                    foreach ($data['pecas'] ?? [] as $item) {
                        $orcamento->pecas()->create($item);
                        $total += $item['quantidade'] * $item['preco_unitario'];
                    }
                } else {
                    $total += $orcamento->pecas->sum(fn($p) => $p->quantidade * $p->preco_unitario);
                }

                if (array_key_exists('mao_de_obra', $data)) {
                    $orcamento->maoDeObra()->delete();
                    foreach ($data['mao_de_obra'] ?? [] as $item) {
                        $orcamento->maoDeObra()->create($item);
                        $total += $item['valor'];
                    }
                } else {
                    $total += $orcamento->maoDeObra->sum('valor');
                }

                $orcamento->update(['valor_total' => $total]);
            }

            DB::commit();
            return response()->json($orcamento->fresh()->load(['servicos', 'pecas.peca', 'maoDeObra']));
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao atualizar orçamento.'], 500);
        }
    }

    public function atualizarStatus(Request $request, Orcamento $orcamento)
    {
        $request->validate([
            'status' => 'required|in:orcamento,aprovado,em_servico,concluido,cancelado',
        ]);

        $agora = now();
        $campos = ['status' => $request->status];

        match ($request->status) {
            'aprovado'   => $campos['aprovado_em']   = $agora,
            'em_servico' => $campos['iniciado_em']   = $agora,
            'concluido'  => $campos['concluido_em']  = $agora,
            default      => null,
        };

        $orcamento->update($campos);
        return response()->json($orcamento);
    }

    public function gerarOs(Orcamento $orcamento)
    {
        if ($orcamento->ordemServico()->exists()) {
            return response()->json(['message' => 'OS já gerada para este orçamento.'], 409);
        }

        if (! in_array($orcamento->status, ['aprovado', 'em_servico'])) {
            return response()->json(['message' => 'Orçamento precisa estar aprovado.'], 422);
        }

        DB::beginTransaction();
        try {
            $os = OrdemServico::create([
                'numero_os'   => OrdemServico::gerarNumeroOs(),
                'orcamento_id'=> $orcamento->id,
                'cliente_id'  => $orcamento->cliente_id,
                'veiculo_id'  => $orcamento->veiculo_id,
                'descricao'   => $orcamento->queixa_cliente,
                'valor_total' => $orcamento->valor_total,
            ]);

            // Débita peças do estoque
            foreach ($orcamento->pecas as $item) {
                $item->peca->decrement('quantidade', $item->quantidade);
                $os->pecas()->create([
                    'peca_id'        => $item->peca_id,
                    'quantidade'     => $item->quantidade,
                    'preco_unitario' => $item->preco_unitario,
                ]);
            }

            // Gera lembretes automáticos via catálogo
            foreach ($orcamento->servicos as $servico) {
                $catalogo = CatalogoServico::where('nome', 'like', "%{$servico->servico_nome}%")
                    ->whereNotNull('dias_lembrete')
                    ->first();

                if ($catalogo) {
                    Lembrete::create([
                        'cliente_id'   => $orcamento->cliente_id,
                        'veiculo_id'   => $orcamento->veiculo_id,
                        'os_id'        => $os->id,
                        'servico_nome' => $servico->servico_nome,
                        'data_servico' => now(),
                        'data_lembrete'=> now()->addDays($catalogo->dias_lembrete),
                    ]);
                }
            }

            $orcamento->update(['status' => 'em_servico', 'iniciado_em' => now()]);

            DB::commit();
            return response()->json($os->load(['cliente', 'veiculo']), 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao gerar OS.'], 500);
        }
    }

    public function destroy(Orcamento $orcamento)
    {
        if ($orcamento->ordemServico()->exists()) {
            return response()->json([
                'message' => 'Orçamento possui OS vinculada e não pode ser excluído.',
            ], 409);
        }

        $orcamento->servicos()->delete();
        $orcamento->pecas()->delete();
        $orcamento->maoDeObra()->delete();
        $orcamento->delete();

        return response()->json(['message' => 'Orçamento excluído.']);
    }
}
