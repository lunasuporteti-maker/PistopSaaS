<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteWebController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::when($request->search, fn($q) =>
            $q->where('nome', 'like', "%{$request->search}%")
              ->orWhere('telefone', 'like', "%{$request->search}%")
              ->orWhere('cpf', 'like', "%{$request->search}%")
        )->orderBy('nome')->paginate(20);

        return view('pitstop.clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('pitstop.clientes.form', ['cliente' => new Cliente]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'      => ['required', 'string', 'max:120', 'regex:/^[\p{L}\s\-]+$/u'],
            'telefone'  => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:120',
            'cpf'       => 'nullable|string|max:14|unique:clientes,cpf',
            'cep'       => 'nullable|string|max:9',
            'logradouro'=> 'nullable|string|max:150',
            'numero'    => 'nullable|string|max:20',
            'bairro'    => 'nullable|string|max:80',
            'cidade'    => 'nullable|string|max:80',
            'uf'        => 'nullable|string|max:2',
        ], ['nome.regex' => 'O nome deve conter apenas letras e espaços.']);

        $data['nome'] = strtoupper($data['nome']);
        if (! empty($data['logradouro'])) {
            $data['logradouro'] = strtoupper($data['logradouro']);
            $data['bairro']     = strtoupper($data['bairro'] ?? '');
            $data['cidade']     = strtoupper($data['cidade'] ?? '');
            $data['uf']         = strtoupper($data['uf'] ?? '');
        }

        Cliente::create($data);
        return redirect()->route('clientes.index')->with('success', 'Cliente cadastrado.');
    }

    public function show(Cliente $cliente)
    {
        return $this->ficha($cliente);
    }

    public function ficha(Cliente $cliente)
    {
        $cliente->load(['veiculos', 'orcamentos.veiculo', 'ordensServico.veiculo']);
        return view('pitstop.clientes.ficha', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('pitstop.clientes.form', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nome'      => ['required', 'string', 'max:120', 'regex:/^[\p{L}\s\-]+$/u'],
            'telefone'  => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:120',
            'cpf'       => 'nullable|string|max:14|unique:clientes,cpf,' . $cliente->id,
            'cep'       => 'nullable|string|max:9',
            'logradouro'=> 'nullable|string|max:150',
            'numero'    => 'nullable|string|max:20',
            'bairro'    => 'nullable|string|max:80',
            'cidade'    => 'nullable|string|max:80',
            'uf'        => 'nullable|string|max:2',
        ], ['nome.regex' => 'O nome deve conter apenas letras e espaços.']);

        $data['nome'] = strtoupper($data['nome']);
        if (! empty($data['logradouro'])) {
            $data['logradouro'] = strtoupper($data['logradouro']);
            $data['bairro']     = strtoupper($data['bairro'] ?? '');
            $data['cidade']     = strtoupper($data['cidade'] ?? '');
            $data['uf']         = strtoupper($data['uf'] ?? '');
        }

        $cliente->update($data);
        return redirect()->route('clientes.show', $cliente)->with('success', 'Cliente atualizado.');
    }

    public function destroy(Cliente $cliente)
    {
        if ($cliente->veiculos()->exists() || $cliente->orcamentos()->exists()) {
            return back()->with('error', 'Cliente possui vínculos e não pode ser excluído.');
        }

        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente excluído.');
    }
}
