<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Peca;
use Illuminate\Http\Request;

class PecaWebController extends Controller
{
    public function index(Request $request)
    {
        $pecas = Peca::when($request->search, fn($q) => $q->where('nome', 'like', "%{$request->search}%"))
            ->when($request->estoque_baixo, fn($q) => $q->whereColumn('quantidade', '<=', 'estoque_minimo'))
            ->orderBy('nome')->paginate(20);

        return view('pitstop.pecas.index', compact('pecas'));
    }

    public function create()
    {
        return view('pitstop.pecas.form', ['peca' => new Peca]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'           => 'required|string|max:200',
            'quantidade'     => 'nullable|integer|min:0',
            'preco_custo'    => 'nullable|numeric|min:0',
            'preco_venda'    => 'nullable|numeric|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
        ]);

        Peca::create($data);
        return redirect()->route('pecas.index')->with('success', 'Peça cadastrada.');
    }

    public function show(Peca $peca)
    {
        return redirect()->route('pecas.edit', $peca);
    }

    public function edit(Peca $peca)
    {
        return view('pitstop.pecas.form', compact('peca'));
    }

    public function update(Request $request, Peca $peca)
    {
        $data = $request->validate([
            'nome'           => 'required|string|max:200',
            'quantidade'     => 'nullable|integer|min:0',
            'preco_custo'    => 'nullable|numeric|min:0',
            'preco_venda'    => 'nullable|numeric|min:0',
            'estoque_minimo' => 'nullable|integer|min:0',
        ]);

        $peca->update($data);
        return redirect()->route('pecas.index')->with('success', 'Peça atualizada.');
    }

    public function destroy(Peca $peca)
    {
        if ($peca->osPecas()->exists()) {
            return back()->with('error', 'Peça está vinculada a OS.');
        }
        $peca->delete();
        return redirect()->route('pecas.index')->with('success', 'Peça excluída.');
    }
}
