<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Veiculo;
use App\Models\HistoricoKm;
use Illuminate\Http\Request;

class VeiculoController extends Controller
{
    public function index()
    {
        return response()->json(Veiculo::with('cliente')->orderBy('id', 'desc')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'marca'      => 'nullable|string|max:50',
            'modelo'     => 'nullable|string|max:100',
            'ano'        => 'nullable|integer|min:1900|max:2100',
            'placa'      => 'nullable|string|max:20|unique:veiculos,placa',
            'cor'        => 'nullable|string|max:30',
            'km_atual'   => 'nullable|integer|min:0',
        ]);

        $veiculo = Veiculo::create($data);

        if ($data['km_atual'] ?? null) {
            HistoricoKm::create([
                'veiculo_id' => $veiculo->id,
                'km'         => $data['km_atual'],
                'observacao' => 'Cadastro inicial',
            ]);
        }

        return response()->json($veiculo, 201);
    }

    public function show(Veiculo $veiculo)
    {
        return response()->json($veiculo->load('cliente'));
    }

    public function update(Request $request, Veiculo $veiculo)
    {
        $data = $request->validate([
            'marca'    => 'nullable|string|max:50',
            'modelo'   => 'nullable|string|max:100',
            'ano'      => 'nullable|integer|min:1900|max:2100',
            'placa'    => 'nullable|string|max:20|unique:veiculos,placa,' . $veiculo->id,
            'cor'      => 'nullable|string|max:30',
            'km_atual' => 'nullable|integer|min:0',
        ]);

        if (isset($data['km_atual']) && $data['km_atual'] !== $veiculo->km_atual) {
            HistoricoKm::create([
                'veiculo_id' => $veiculo->id,
                'km'         => $data['km_atual'],
            ]);
        }

        $veiculo->update($data);
        return response()->json($veiculo);
    }

    public function destroy(Veiculo $veiculo)
    {
        if ($veiculo->orcamentos()->exists() || $veiculo->ordensServico()->exists()) {
            return response()->json([
                'message' => 'Veículo possui vínculos e não pode ser excluído.',
            ], 409);
        }

        $veiculo->delete();
        return response()->json(['message' => 'Veículo excluído.']);
    }

    public function porCliente($clienteId)
    {
        $veiculos = Veiculo::where('cliente_id', $clienteId)->get();
        return response()->json($veiculos);
    }

    public function historicoKm(Veiculo $veiculo)
    {
        $historico = $veiculo->historicoKm()->orderBy('created_at', 'desc')->get();
        return response()->json($historico);
    }
}
