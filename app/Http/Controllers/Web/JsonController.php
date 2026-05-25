<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Veiculo;
use Illuminate\Http\Request;

class JsonController extends Controller
{
    /**
     * Retorna os veículos de um cliente (para selects dinâmicos).
     */
    public function veiculosPorCliente(int $clienteId)
    {
        $veiculos = Veiculo::where('cliente_id', $clienteId)
            ->select('id', 'marca', 'modelo', 'placa', 'ano')
            ->orderBy('modelo')
            ->get();

        return response()->json($veiculos);
    }

    /**
     * Cria um novo cliente via modal (formulário de orçamento).
     */
    public function storeCliente(Request $request)
    {
        $data = $request->validate([
            'nome'     => 'required|string|max:100',
            'telefone' => 'nullable|string|max:20',
            'cpf'      => 'nullable|string|max:14',
        ]);

        $cliente = Cliente::create($data);

        return response()->json($cliente);
    }

    /**
     * Cria um novo veículo via modal (formulário de orçamento).
     */
    public function storeVeiculo(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'marca'      => 'nullable|string|max:50',
            'modelo'     => 'nullable|string|max:100',
            'ano'        => 'nullable|integer',
            'placa'      => 'nullable|string|max:20',
            'cor'        => 'nullable|string|max:30',
        ]);

        $veiculo = Veiculo::create($data);

        return response()->json($veiculo);
    }
}
