<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaoDeObra;
use Illuminate\Http\Request;

class MaoDeObraController extends Controller
{
    public function index()
    {
        return response()->json(MaoDeObra::where('ativo', true)->orderBy('nome')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'                  => 'required|string|max:200',
            'descricao'             => 'nullable|string',
            'preco'                 => 'required|numeric|min:0',
            'tempo_estimado_horas'  => 'nullable|numeric|min:0',
            'ativo'                 => 'boolean',
        ]);

        return response()->json(MaoDeObra::create($data), 201);
    }

    public function update(Request $request, MaoDeObra $maoDeObra)
    {
        $data = $request->validate([
            'nome'                  => 'sometimes|string|max:200',
            'descricao'             => 'nullable|string',
            'preco'                 => 'sometimes|numeric|min:0',
            'tempo_estimado_horas'  => 'nullable|numeric|min:0',
            'ativo'                 => 'boolean',
        ]);

        $maoDeObra->update($data);
        return response()->json($maoDeObra);
    }

    public function destroy(MaoDeObra $maoDeObra)
    {
        $maoDeObra->update(['ativo' => false]);
        return response()->json(['message' => 'Mão de obra desativada.']);
    }
}
