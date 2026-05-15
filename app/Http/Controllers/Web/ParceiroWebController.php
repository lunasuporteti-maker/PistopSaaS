<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Parceiro;
use Illuminate\Http\Request;

class ParceiroWebController extends Controller
{
    public function index()
    {
        $parceiros = Parceiro::orderBy('nome')->paginate(20);
        return view('pitstop.parceiros.index', compact('parceiros'));
    }

    public function create()
    {
        return view('pitstop.parceiros.form', ['parceiro' => new Parceiro]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'             => 'required|string|max:100',
            'servico_prestado' => 'nullable|string|max:200',
            'telefone'         => 'nullable|string|max:20',
        ]);

        Parceiro::create($data);
        return redirect()->route('parceiros.index')->with('success', 'Parceiro cadastrado.');
    }

    public function edit(Parceiro $parceiro)
    {
        return view('pitstop.parceiros.form', compact('parceiro'));
    }

    public function update(Request $request, Parceiro $parceiro)
    {
        $data = $request->validate([
            'nome'             => 'required|string|max:100',
            'servico_prestado' => 'nullable|string|max:200',
            'telefone'         => 'nullable|string|max:20',
            'ativo'            => 'boolean',
        ]);

        $parceiro->update($data);
        return redirect()->route('parceiros.index')->with('success', 'Parceiro atualizado.');
    }

    public function destroy(Parceiro $parceiro)
    {
        $parceiro->delete();
        return redirect()->route('parceiros.index')->with('success', 'Parceiro excluído.');
    }
}
