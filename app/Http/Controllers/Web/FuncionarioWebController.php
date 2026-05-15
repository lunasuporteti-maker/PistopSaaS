<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Funcionario;
use App\Models\MaoDeObra;
use Illuminate\Http\Request;

class FuncionarioWebController extends Controller
{
    public function index()
    {
        $funcionarios = Funcionario::orderBy('nome')->paginate(20);
        return view('pitstop.funcionarios.index', compact('funcionarios'));
    }

    public function create()
    {
        return view('pitstop.funcionarios.form', ['funcionario' => new Funcionario]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'         => 'required|string|max:100',
            'cargo'        => 'nullable|string|max:50',
            'salario_base' => 'nullable|numeric|min:0',
            'telefone'     => 'nullable|string|max:20',
        ]);

        Funcionario::create($data);
        return redirect()->route('funcionarios.index')->with('success', 'Funcionário cadastrado.');
    }

    public function edit(Funcionario $funcionario)
    {
        return view('pitstop.funcionarios.form', compact('funcionario'));
    }

    public function update(Request $request, Funcionario $funcionario)
    {
        $data = $request->validate([
            'nome'         => 'required|string|max:100',
            'cargo'        => 'nullable|string|max:50',
            'salario_base' => 'nullable|numeric|min:0',
            'telefone'     => 'nullable|string|max:20',
            'ativo'        => 'boolean',
        ]);

        $funcionario->update($data);
        return redirect()->route('funcionarios.index')->with('success', 'Funcionário atualizado.');
    }

    public function destroy(Funcionario $funcionario)
    {
        $funcionario->delete();
        return redirect()->route('funcionarios.index')->with('success', 'Funcionário excluído.');
    }
}
