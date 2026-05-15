<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServico;
use Illuminate\Http\Request;

class CatalogoServicosWebController extends Controller
{
    public function index()
    {
        $servicos = CatalogoServico::orderBy('nome')->paginate(20);
        return view('pitstop.catalogo-servicos.index', compact('servicos'));
    }

    public function create()
    {
        return view('pitstop.catalogo-servicos.form', ['servico' => new CatalogoServico]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'                 => 'required|string|max:100',
            'descricao'            => 'nullable|string',
            'preco_sugerido'       => 'nullable|numeric|min:0',
            'tempo_estimado_horas' => 'nullable|numeric|min:0|max:999',
            'dias_lembrete'        => 'nullable|integer|min:1|max:3650',
        ]);

        CatalogoServico::create($data);
        return redirect()->route('catalogo-servicos.index')->with('success', 'Serviço cadastrado no catálogo.');
    }

    public function edit(CatalogoServico $catalogoServico)
    {
        return view('pitstop.catalogo-servicos.form', ['servico' => $catalogoServico]);
    }

    public function update(Request $request, CatalogoServico $catalogoServico)
    {
        $data = $request->validate([
            'nome'                 => 'required|string|max:100',
            'descricao'            => 'nullable|string',
            'preco_sugerido'       => 'nullable|numeric|min:0',
            'tempo_estimado_horas' => 'nullable|numeric|min:0|max:999',
            'dias_lembrete'        => 'nullable|integer|min:1|max:3650',
            'ativo'                => 'boolean',
        ]);

        $catalogoServico->update($data);
        return redirect()->route('catalogo-servicos.index')->with('success', 'Serviço atualizado.');
    }

    public function destroy(CatalogoServico $catalogoServico)
    {
        $catalogoServico->update(['ativo' => false]);
        return redirect()->route('catalogo-servicos.index')->with('success', 'Serviço desativado do catálogo.');
    }
}
