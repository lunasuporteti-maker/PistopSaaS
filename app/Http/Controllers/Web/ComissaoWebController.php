<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Comissao;
use App\Models\Funcionario;
use App\Models\OrdemServico;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComissaoWebController extends Controller
{
    public function index(Request $request)
    {
        $mes          = $request->mes ?? now()->format('Y-m');
        $funcionarioId = $request->funcionario_id;

        $query = Comissao::with(['funcionario', 'ordemServico.cliente', 'ordemServico.veiculo'])
            ->whereHas('ordemServico', fn($q) => $q->whereRaw("TO_CHAR(finalizado_em, 'YYYY-MM') = ?", [$mes]));

        if ($funcionarioId) {
            $query->where('funcionario_id', $funcionarioId);
        }

        $comissoes = $query->orderByDesc('created_at')->paginate(30);

        $totais = Comissao::whereHas('ordemServico', fn($q) => $q->whereRaw("TO_CHAR(finalizado_em, 'YYYY-MM') = ?", [$mes]))
            ->when($funcionarioId, fn($q) => $q->where('funcionario_id', $funcionarioId))
            ->selectRaw('SUM(valor) as total, SUM(CASE WHEN pago THEN valor ELSE 0 END) as total_pago')
            ->first();

        $funcionarios = Funcionario::where('ativo', true)->orderBy('nome')->get();
        $ordens       = OrdemServico::whereNotNull('finalizado_em')
            ->with(['cliente', 'veiculo'])
            ->orderByDesc('finalizado_em')
            ->limit(50)->get();

        return view('pitstop.comissoes.index', compact(
            'comissoes', 'totais', 'mes', 'funcionarioId', 'funcionarios', 'ordens'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'os_id'          => 'nullable|exists:ordens_servico,id',
            'percentual'     => 'nullable|numeric|min:0|max:100',
            'valor'          => 'required|numeric|min:0.01',
        ]);

        Comissao::create($data);

        return back()->with('success', 'Comissão registrada.');
    }

    public function pagar(Comissao $comissao)
    {
        $comissao->update(['pago' => true, 'data_pagamento' => now()]);
        return back()->with('success', 'Comissão marcada como paga.');
    }

    public function destroy(Comissao $comissao)
    {
        $comissao->delete();
        return back()->with('success', 'Comissão removida.');
    }
}
