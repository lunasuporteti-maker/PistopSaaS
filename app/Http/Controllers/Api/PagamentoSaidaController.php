<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PagamentoSaida;
use Illuminate\Http\Request;

class PagamentoSaidaController extends Controller
{
    public function index(Request $request)
    {
        $query = PagamentoSaida::with(['funcionario', 'parceiro']);

        if ($request->mes) {
            $query->where('mes_referencia', $request->mes);
        }

        if ($request->categoria) {
            $query->where('categoria', $request->categoria);
        }

        return response()->json($query->orderBy('data_pagamento', 'desc')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo'            => 'required|string|max:30',
            'descricao'       => 'nullable|string|max:200',
            'valor'           => 'required|numeric|min:0.01',
            'funcionario_id'  => 'nullable|exists:funcionarios,id',
            'parceiro_id'     => 'nullable|exists:parceiros,id',
            'data_pagamento'  => 'required|date',
            'mes_referencia'  => 'nullable|string|max:7',
            'categoria'       => 'nullable|string|max:30',
        ]);

        return response()->json(PagamentoSaida::create($data), 201);
    }

    public function destroy(PagamentoSaida $pagamentoSaida)
    {
        $pagamentoSaida->delete();
        return response()->json(['message' => 'Pagamento excluído.']);
    }
}
