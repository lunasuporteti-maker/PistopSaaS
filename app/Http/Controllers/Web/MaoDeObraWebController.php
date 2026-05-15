<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MaoDeObra;
use Illuminate\Http\Request;

class MaoDeObraWebController extends Controller
{
    public function index()
    {
        $itens = MaoDeObra::orderBy('nome')->paginate(20);
        return view('pitstop.mao-de-obra.index', compact('itens'));
    }

    public function create()
    {
        return view('pitstop.mao-de-obra.form', ['item' => new MaoDeObra]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'                 => 'required|string|max:200',
            'descricao'            => 'nullable|string',
            'preco'                => 'required|numeric|min:0',
            'tempo_estimado_horas' => 'nullable|numeric|min:0|max:999',
        ]);

        MaoDeObra::create($data);
        return redirect()->route('mao-de-obra.index')->with('success', 'Mão de obra cadastrada.');
    }

    public function edit(MaoDeObra $maoDeObra)
    {
        return view('pitstop.mao-de-obra.form', ['item' => $maoDeObra]);
    }

    public function update(Request $request, MaoDeObra $maoDeObra)
    {
        $data = $request->validate([
            'nome'                 => 'required|string|max:200',
            'descricao'            => 'nullable|string',
            'preco'                => 'required|numeric|min:0',
            'tempo_estimado_horas' => 'nullable|numeric|min:0|max:999',
            'ativo'                => 'boolean',
        ]);

        $maoDeObra->update($data);
        return redirect()->route('mao-de-obra.index')->with('success', 'Mão de obra atualizada.');
    }

    public function destroy(MaoDeObra $maoDeObra)
    {
        $maoDeObra->update(['ativo' => false]);
        return redirect()->route('mao-de-obra.index')->with('success', 'Mão de obra desativada.');
    }
}
