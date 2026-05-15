<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use Illuminate\Http\Request;

class FuncionarioController extends Controller
{
    public function index()
    {
        return response()->json(Funcionario::orderBy('nome')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'         => 'required|string|max:100',
            'cargo'        => 'nullable|string|max:50',
            'salario_base' => 'nullable|numeric|min:0',
            'telefone'     => 'nullable|string|max:20',
            'ativo'        => 'boolean',
        ]);

        return response()->json(Funcionario::create($data), 201);
    }

    public function update(Request $request, Funcionario $funcionario)
    {
        $data = $request->validate([
            'nome'         => 'sometimes|string|max:100',
            'cargo'        => 'nullable|string|max:50',
            'salario_base' => 'nullable|numeric|min:0',
            'telefone'     => 'nullable|string|max:20',
            'ativo'        => 'boolean',
        ]);

        $funcionario->update($data);
        return response()->json($funcionario);
    }

    public function destroy(Funcionario $funcionario)
    {
        if ($funcionario->pagamentosSaida()->exists() || $funcionario->comissoes()->exists()) {
            return response()->json([
                'message' => 'Funcionário possui registros financeiros vinculados.',
            ], 409);
        }

        $funcionario->delete();
        return response()->json(['message' => 'Funcionário excluído.']);
    }
}
