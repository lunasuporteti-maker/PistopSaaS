<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use App\Models\Orcamento;
use Illuminate\Http\Request;

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
        $request->validate([
            'pagamentos'         => 'required|array|min:1',
            'pagamentos.*.forma' => 'required|string',
            'pagamentos.*.valor' => 'required|numeric|min:0.01',
        ]);

        foreach ($request->pagamentos as $pag) {
            $ordem->pagamentos()->create($pag);
        }

        $ordem->update(['finalizado_em' => now()]);

        if ($ordem->orcamento) {
            $ordem->orcamento->update(['status' => 'concluido', 'concluido_em' => now()]);
        }

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
