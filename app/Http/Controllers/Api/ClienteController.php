<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Orcamento;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nome', 'like', "%{$request->search}%")
                  ->orWhere('telefone', 'like', "%{$request->search}%")
                  ->orWhere('cpf', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        return response()->json($query->orderBy('nome')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'     => 'required|string|max:100',
            'telefone' => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:100',
            'cpf'      => 'nullable|string|max:14|unique:clientes,cpf',
            'endereco' => 'nullable|string',
        ]);

        $cliente = Cliente::create($data);
        return response()->json($cliente, 201);
    }

    public function show(Cliente $cliente)
    {
        return response()->json($cliente->load(['veiculos']));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nome'     => 'sometimes|string|max:100',
            'telefone' => 'nullable|string|max:20',
            'email'    => 'nullable|email|max:100',
            'cpf'      => 'nullable|string|max:14|unique:clientes,cpf,' . $cliente->id,
            'endereco' => 'nullable|string',
        ]);

        $cliente->update($data);
        return response()->json($cliente);
    }

    public function destroy(Cliente $cliente)
    {
        $temVinculos = $cliente->veiculos()->exists()
            || $cliente->orcamentos()->exists()
            || $cliente->ordensServico()->exists();

        if ($temVinculos) {
            return response()->json([
                'message' => 'Cliente possui vínculos e não pode ser excluído.',
            ], 409);
        }

        $cliente->delete();
        return response()->json(['message' => 'Cliente excluído.']);
    }

    public function ficha(Cliente $cliente)
    {
        $cliente->load([
            'veiculos',
            'orcamentos.veiculo',
            'ordensServico.veiculo',
        ]);

        return response()->json($cliente);
    }

    public function semRetorno()
    {
        $limite = Carbon::now()->subMonths(6);

        $clientes = Cliente::whereDoesntHave('ordensServico', function ($q) use ($limite) {
            $q->where('created_at', '>=', $limite);
        })->get();

        return response()->json($clientes);
    }
}
