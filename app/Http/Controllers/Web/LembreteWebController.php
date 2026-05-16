<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Lembrete;
use Illuminate\Http\Request;

class LembreteWebController extends Controller
{
    public function index()
    {
        $lembretes = Lembrete::with(['cliente', 'veiculo'])
            ->where('status', 'pendente')
            ->orderBy('data_lembrete')
            ->paginate(20);

        $clientes = Cliente::orderBy('nome')->get();

        return view('pitstop.lembretes.index', compact('lembretes', 'clientes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'   => 'required|exists:clientes,id',
            'veiculo_id'   => 'nullable|exists:veiculos,id',
            'servico_nome' => 'required|string|max:200',
            'data_lembrete'=> 'required|date|after_or_equal:today',
        ], [
            'data_lembrete.after_or_equal' => 'A data do lembrete não pode ser no passado.',
        ]);

        $data['status']       = 'pendente';
        $data['servico_nome'] = strtoupper($data['servico_nome']);

        Lembrete::create($data);

        return back()->with('success', 'Lembrete cadastrado com sucesso!');
    }

    public function update(Request $request, Lembrete $lembrete)
    {
        $request->validate(['status' => 'required|in:pendente,enviado,cancelado']);
        $lembrete->update(['status' => $request->status]);
        return back()->with('success', 'Lembrete atualizado.');
    }

    public function destroy(Lembrete $lembrete)
    {
        $lembrete->delete();
        return back()->with('success', 'Lembrete excluído.');
    }
}
