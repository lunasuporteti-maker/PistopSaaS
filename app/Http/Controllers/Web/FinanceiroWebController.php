<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PagamentoSaida;
use App\Models\Funcionario;
use App\Models\Parceiro;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinanceiroWebController extends Controller
{
    public function index(Request $request)
    {
        $mes  = $request->mes ?? now()->format('Y-m');
        $saidas = PagamentoSaida::with(['funcionario', 'parceiro'])
            ->where('mes_referencia', $mes)
            ->orderBy('data_pagamento', 'desc')
            ->paginate(20);

        $funcionarios = Funcionario::where('ativo', true)->orderBy('nome')->get();
        $parceiros    = Parceiro::where('ativo', true)->orderBy('nome')->get();

        return view('pitstop.financeiro.index', compact('saidas', 'mes', 'funcionarios', 'parceiros'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo'           => 'required|string|max:30',
            'descricao'      => 'nullable|string|max:200',
            'valor'          => 'required|numeric|min:0.01',
            'funcionario_id' => 'nullable|exists:funcionarios,id',
            'parceiro_id'    => 'nullable|exists:parceiros,id',
            'data_pagamento' => 'required|date',
            'mes_referencia' => 'nullable|string|max:7',
            'categoria'      => 'nullable|string|max:30',
        ]);

        PagamentoSaida::create($data);
        return back()->with('success', 'Lançamento registrado.');
    }

    public function update(Request $request, PagamentoSaida $item)
    {
        $data = $request->validate([
            'tipo'           => 'required|string|max:30',
            'descricao'      => 'nullable|string|max:200',
            'valor'          => 'required|numeric|min:0.01',
            'funcionario_id' => 'nullable|exists:funcionarios,id',
            'parceiro_id'    => 'nullable|exists:parceiros,id',
            'data_pagamento' => 'required|date',
            'categoria'      => 'nullable|string|max:30',
        ]);

        $item->update($data);
        return back()->with('success', 'Lançamento atualizado.');
    }

    public function destroy(PagamentoSaida $item)
    {
        $item->delete();
        return back()->with('success', 'Lançamento excluído.');
    }
}
