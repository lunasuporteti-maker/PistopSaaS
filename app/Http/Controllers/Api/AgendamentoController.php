<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AgendamentoController extends Controller
{
    public function index(Request $request)
    {
        $query = Agendamento::with(['cliente', 'veiculo']);

        if ($request->data) {
            $query->whereDate('data_hora', $request->data);
        } elseif ($request->mes) {
            $query->whereMonth('data_hora', Carbon::parse($request->mes)->month)
                  ->whereYear('data_hora', Carbon::parse($request->mes)->year);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderBy('data_hora')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'  => 'required|exists:clientes,id',
            'veiculo_id'  => 'nullable|exists:veiculos,id',
            'data_hora'   => 'required|date',
            'servico'     => 'nullable|string|max:200',
            'observacao'  => 'nullable|string',
        ]);

        return response()->json(Agendamento::create($data), 201);
    }

    public function update(Request $request, Agendamento $agendamento)
    {
        $data = $request->validate([
            'data_hora'   => 'sometimes|date',
            'servico'     => 'nullable|string|max:200',
            'status'      => 'sometimes|in:agendado,confirmado,realizado,cancelado',
            'observacao'  => 'nullable|string',
            'resultado'   => 'nullable|string',
        ]);

        $agendamento->update($data);
        return response()->json($agendamento);
    }

    public function destroy(Agendamento $agendamento)
    {
        $agendamento->delete();
        return response()->json(['message' => 'Agendamento excluído.']);
    }
}
