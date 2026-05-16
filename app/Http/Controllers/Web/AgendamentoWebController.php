<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AgendamentoWebController extends Controller
{
    public function index(Request $request)
    {
        $dataInicio = $request->data_inicio ?? today()->toDateString();
        $dataFim    = $request->data_fim    ?? today()->toDateString();

        $agendamentos = Agendamento::with(['cliente', 'veiculo'])
            ->whereDate('data_hora', '>=', $dataInicio)
            ->whereDate('data_hora', '<=', $dataFim)
            ->orderBy('data_hora')
            ->get();

        return view('pitstop.agendamentos.index', compact('agendamentos', 'dataInicio', 'dataFim'));
    }

    public function concluir(Agendamento $agendamento)
    {
        $agendamento->update(['status' => 'realizado']);
        return back()->with('success', 'Agendamento marcado como concluído.');
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nome')->get();
        return view('pitstop.agendamentos.form', ['agendamento' => new Agendamento, 'clientes' => $clientes]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'veiculo_id' => 'nullable|exists:veiculos,id',
            'data_hora'  => 'required|date',
            'servico'    => 'nullable|string|max:200',
            'observacao' => 'nullable|string',
        ]);

        Agendamento::create($data);
        return redirect()->route('agendamentos.index')->with('success', 'Agendamento criado.');
    }

    public function show(Agendamento $agendamento)
    {
        return redirect()->route('agendamentos.edit', $agendamento);
    }

    public function edit(Agendamento $agendamento)
    {
        $clientes = Cliente::orderBy('nome')->get();
        return view('pitstop.agendamentos.form', compact('agendamento', 'clientes'));
    }

    public function update(Request $request, Agendamento $agendamento)
    {
        $data = $request->validate([
            'data_hora'  => 'required|date',
            'servico'    => 'nullable|string|max:200',
            'status'     => 'required|in:agendado,confirmado,realizado,cancelado',
            'observacao' => 'nullable|string',
            'resultado'  => 'nullable|string',
        ]);

        $agendamento->update($data);
        return redirect()->route('agendamentos.index')->with('success', 'Agendamento atualizado.');
    }

    public function destroy(Agendamento $agendamento)
    {
        $agendamento->delete();
        return redirect()->route('agendamentos.index')->with('success', 'Agendamento excluído.');
    }
}
