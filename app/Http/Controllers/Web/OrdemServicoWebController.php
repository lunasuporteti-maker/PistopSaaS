<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use App\Models\Orcamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdemServicoWebController extends Controller
{
    public function index()
    {
        $ordens = OrdemServico::with(['cliente', 'veiculo'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('pitstop.ordens.index', compact('ordens'));
    }

    public function fila()
    {
        $fila = Orcamento::with(['cliente', 'veiculo'])
            ->whereIn('status', ['aprovado', 'em_servico'])
            ->orderBy('posicao_fila')
            ->get();

        return view('pitstop.fila', compact('fila'));
    }

    public function show(OrdemServico $ordem)
    {
        $ordem->load(['cliente', 'veiculo', 'pecas.peca', 'pagamentos', 'orcamento.servicos', 'orcamento.maoDeObra']);
        return view('pitstop.ordens.show', compact('ordem'));
    }

    public function finalizar(Request $request, OrdemServico $ordem)
    {
        // Idempotência: evita pagamento duplicado se a OS já foi finalizada
        // (reenvio do form, corrida com a conclusão pelo Kanban, etc.)
        if ($ordem->finalizado_em) {
            return redirect()->route('ordens.show', $ordem)
                ->with('info', 'Esta OS já foi finalizada.');
        }

        $request->validate([
            'pagamentos'         => 'required|array|min:1',
            'pagamentos.*.forma' => 'required|string',
            'pagamentos.*.valor' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request, $ordem) {
            foreach ($request->pagamentos as $pag) {
                $ordem->pagamentos()->create([
                    'forma' => $pag['forma'],
                    'valor' => $pag['valor'],
                ]);
            }

            // Mesma conclusão do Kanban: marca a OS como concluída de fato
            $ordem->update([
                'status'        => 'concluido',
                'concluido_em'  => now(),
                'finalizado_em' => now(),
            ]);

            // Baixa estoque das peças do orçamento, se ainda não foi feito
            // (no fluxo normal o gerarOs já baixou — a guarda evita baixa dupla)
            if ($ordem->pecas()->doesntExist() && $ordem->orcamento) {
                foreach ($ordem->orcamento->pecas()->with('peca')->get() as $item) {
                    $item->peca->decrement('quantidade', $item->quantidade);
                    $ordem->pecas()->create([
                        'peca_id'        => $item->peca_id,
                        'quantidade'     => $item->quantidade,
                        'preco_unitario' => $item->preco_unitario,
                    ]);
                }
            }

            if ($ordem->orcamento) {
                $ordem->orcamento->update(['status' => 'concluido', 'concluido_em' => now()]);
            }
        });

        return redirect()->route('ordens.show', $ordem)
            ->with('success', 'OS finalizada com sucesso!')
            ->with('show_whatsapp', true);
    }

    public function edit(OrdemServico $ordem)
    {
        $this->authorize('acima_de_mecanico');
        return view('pitstop.ordens.edit', compact('ordem'));
    }

    public function update(Request $request, OrdemServico $ordem)
    {
        $this->authorize('acima_de_mecanico');

        $data = $request->validate([
            'descricao'    => 'nullable|string',
            'valor_total'  => 'required|numeric|min:0',
            'garantia_dias'=> 'nullable|integer|min:0',
        ]);

        $ordem->update($data);
        return redirect()->route('ordens.show', $ordem)->with('success', 'OS atualizada.');
    }

    public function destroy(OrdemServico $ordem)
    {
        $this->authorize('acima_de_mecanico');

        foreach ($ordem->pecas as $item) {
            $item->peca->increment('quantidade', $item->quantidade);
        }

        $ordem->delete();
        return redirect()->route('ordens.index')->with('success', 'OS excluída.');
    }
}
