<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServico;
use Illuminate\Http\Request;

class CatalogoServicoController extends Controller
{
    public function index()
    {
        return response()->json(CatalogoServico::orderBy('nome')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'                  => 'required|string|max:100',
            'descricao'             => 'nullable|string',
            'preco_sugerido'        => 'nullable|numeric|min:0',
            'tempo_estimado_horas'  => 'nullable|numeric|min:0',
            'dias_lembrete'         => 'nullable|integer|min:1',
            'ativo'                 => 'boolean',
        ]);

        return response()->json(CatalogoServico::create($data), 201);
    }

    public function update(Request $request, CatalogoServico $catalogoServico)
    {
        $data = $request->validate([
            'nome'                  => 'sometimes|string|max:100',
            'descricao'             => 'nullable|string',
            'preco_sugerido'        => 'nullable|numeric|min:0',
            'tempo_estimado_horas'  => 'nullable|numeric|min:0',
            'dias_lembrete'         => 'nullable|integer|min:1',
            'ativo'                 => 'boolean',
        ]);

        $catalogoServico->update($data);
        return response()->json($catalogoServico);
    }

    public function destroy(CatalogoServico $catalogoServico)
    {
        $catalogoServico->delete();
        return response()->json(['message' => 'Serviço excluído do catálogo.']);
    }
}
