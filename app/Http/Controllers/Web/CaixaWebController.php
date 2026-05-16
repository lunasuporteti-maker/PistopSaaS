<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Caixa;
use App\Models\PagamentoOs;
use App\Models\PagamentoSaida;
use Illuminate\Http\Request;

class CaixaWebController extends Controller
{
    public function index()
    {
        $caixaHoje    = Caixa::caixaAberto();
        $ultimosCaixas = Caixa::orderBy('data', 'desc')->limit(10)->get();

        $receitaHoje = 0;
        $saidaHoje   = 0;

        if ($caixaHoje) {
            $receitaHoje = PagamentoOs::whereDate('created_at', today())->sum('valor');
            $saidaHoje   = PagamentoSaida::whereDate('data_pagamento', today())->sum('valor');
        }

        return view('pitstop.caixa.index', compact('caixaHoje', 'ultimosCaixas', 'receitaHoje', 'saidaHoje'));
    }

    public function abrir(Request $request)
    {
        if (Caixa::caixaAberto()) {
            return back()->with('error', 'Já existe um caixa aberto hoje.');
        }

        $data = $request->validate([
            'saldo_inicial'       => 'required|numeric|min:0',
            'observacao_abertura' => 'nullable|string|max:300',
        ]);

        Caixa::create(array_merge($data, [
            'data'      => today(),
            'status'    => 'aberto',
            'aberto_em' => now(),
        ]));

        return back()->with('success', 'Caixa aberto com sucesso!');
    }

    public function fechar(Request $request, Caixa $caixa)
    {
        if ($caixa->status === 'fechado') {
            return back()->with('error', 'Este caixa já foi fechado.');
        }

        $data = $request->validate([
            'saldo_final'            => 'required|numeric|min:0',
            'observacao_fechamento'  => 'nullable|string|max:300',
        ]);

        $caixa->update(array_merge($data, [
            'status'     => 'fechado',
            'fechado_em' => now(),
        ]));

        return back()->with('success', 'Caixa fechado com sucesso!');
    }
}
