<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Parceiro;
use Illuminate\Http\Request;

class ParceiroController extends Controller
{
    public function index()
    {
        return response()->json(Parceiro::orderBy('nome')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'             => 'required|string|max:100',
            'servico_prestado' => 'nullable|string|max:200',
            'telefone'         => 'nullable|string|max:20',
            'ativo'            => 'boolean',
        ]);

        return response()->json(Parceiro::create($data), 201);
    }

    public function update(Request $request, Parceiro $parceiro)
    {
        $data = $request->validate([
            'nome'             => 'sometimes|string|max:100',
            'servico_prestado' => 'nullable|string|max:200',
            'telefone'         => 'nullable|string|max:20',
            'ativo'            => 'boolean',
        ]);

        $parceiro->update($data);
        return response()->json($parceiro);
    }

    public function destroy(Parceiro $parceiro)
    {
        if ($parceiro->pagamentosSaida()->exists()) {
            return response()->json([
                'message' => 'Parceiro possui pagamentos vinculados.',
            ], 409);
        }

        $parceiro->delete();
        return response()->json(['message' => 'Parceiro excluído.']);
    }
}
