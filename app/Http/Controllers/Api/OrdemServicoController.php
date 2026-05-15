<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use App\Models\PagamentoOs;
use App\Models\Financeiro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdemServicoController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdemServico::with(['cliente', 'veiculo']);

        if ($request->finalizado === 'false' || $request->finalizado === '0') {
            $query->whereNull('finalizado_em');
        } elseif ($request->finalizado === 'true' || $request->finalizado === '1') {
            $query->whereNotNull('finalizado_em');
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function show(OrdemServico $ordemServico)
    {
        return response()->json($ordemServico->load(['cliente', 'veiculo', 'orcamento']));
    }

    public function detalhes(OrdemServico $ordemServico)
    {
        return response()->json($ordemServico->load([
            'cliente', 'veiculo',
            'pecas.peca', 'pagamentos',
            'orcamento.servicos',
            'orcamento.maoDeObra',
        ]));
    }

    public function finalizar(Request $request, OrdemServico $ordemServico)
    {
        $request->validate([
            'pagamentos'   => 'required|array|min:1',
            'pagamentos.*.forma' => 'required|string',
            'pagamentos.*.valor' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->pagamentos as $pag) {
                PagamentoOs::create([
                    'os_id' => $ordemServico->id,
                    'forma' => $pag['forma'],
                    'valor' => $pag['valor'],
                ]);
            }

            Financeiro::create([
                'os_id'          => $ordemServico->id,
                'tipo'           => 'entrada',
                'descricao'      => "OS {$ordemServico->numero_os}",
                'valor'          => $ordemServico->valor_total,
                'data_pagamento' => now(),
            ]);

            $ordemServico->update(['finalizado_em' => now()]);

            if ($ordemServico->orcamento) {
                $ordemServico->orcamento->update([
                    'status'       => 'concluido',
                    'concluido_em' => now(),
                ]);
            }

            DB::commit();
            return response()->json($ordemServico->fresh()->load(['pagamentos']));
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao finalizar OS.'], 500);
        }
    }

    public function destroy(OrdemServico $ordemServico)
    {
        if ($ordemServico->finalizado_em) {
            return response()->json([
                'message' => 'OS finalizada não pode ser excluída.',
            ], 409);
        }

        DB::beginTransaction();
        try {
            // Devolve peças ao estoque
            foreach ($ordemServico->pecas as $item) {
                $item->peca->increment('quantidade', $item->quantidade);
            }

            $ordemServico->pecas()->delete();
            $ordemServico->pagamentos()->delete();
            $ordemServico->lembretes()->delete();
            $ordemServico->delete();

            DB::commit();
            return response()->json(['message' => 'OS excluída e estoque revertido.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao excluir OS.'], 500);
        }
    }
}
