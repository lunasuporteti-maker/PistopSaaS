<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Lembrete;
use Illuminate\Http\Request;

class LembreteWebController extends Controller
{
    public function index(Request $request)
    {
        $filtroStatus = $request->get('status', 'pendente');

        $query = Lembrete::with(['cliente', 'veiculo'])
            ->orderBy('data_lembrete')
            ->orderBy('hora_lembrete');

        if ($filtroStatus !== 'todos') {
            $query->where('status', $filtroStatus);
        }

        $lembretes = $query->paginate(30)->withQueryString();
        $clientes  = Cliente::orderBy('nome')->get();

        $contadores = [
            'pendente'  => Lembrete::where('status', 'pendente')->count(),
            'concluido' => Lembrete::where('status', 'concluido')->count(),
        ];

        return view('pitstop.lembretes.index', compact('lembretes', 'clientes', 'filtroStatus', 'contadores'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo'        => 'required|string|max:200',
            'cliente_id'    => 'nullable|exists:clientes,id',
            'veiculo_id'    => 'nullable|exists:veiculos,id',
            'observacao'    => 'nullable|string|max:500',
            'data_lembrete' => 'required|date',
            'hora_lembrete' => 'nullable|date_format:H:i',
        ]);

        $data['status']       = 'pendente';
        $data['titulo']       = strtoupper($data['titulo']);
        $data['servico_nome'] = $data['titulo'];

        Lembrete::create($data);

        return back()->with('success', 'Lembrete cadastrado!');
    }

    public function edit(Lembrete $lembrete)
    {
        return response()->json([
            'id'            => $lembrete->id,
            'titulo'        => $lembrete->titulo ?? $lembrete->servico_nome,
            'cliente_id'    => $lembrete->cliente_id,
            'veiculo_id'    => $lembrete->veiculo_id,
            'observacao'    => $lembrete->observacao,
            'data_lembrete' => $lembrete->data_lembrete->format('Y-m-d'),
            'hora_lembrete' => $lembrete->hora_lembrete,
        ]);
    }

    public function update(Request $request, Lembrete $lembrete)
    {
        // Atualização de status (botão concluir/reabrir)
        if ($request->has('status') && ! $request->has('titulo')) {
            $request->validate(['status' => 'required|in:pendente,concluido,cancelado']);
            $lembrete->update(['status' => $request->status]);
            return back()->with('success', 'Lembrete atualizado.');
        }

        // Edição completa via modal
        $data = $request->validate([
            'titulo'        => 'required|string|max:200',
            'cliente_id'    => 'nullable|exists:clientes,id',
            'veiculo_id'    => 'nullable|exists:veiculos,id',
            'observacao'    => 'nullable|string|max:500',
            'data_lembrete' => 'required|date',
            'hora_lembrete' => 'nullable|date_format:H:i',
        ]);

        $data['titulo']       = strtoupper($data['titulo']);
        $data['servico_nome'] = $data['titulo'];

        $lembrete->update($data);

        return back()->with('success', 'Lembrete atualizado!');
    }

    public function destroy(Lembrete $lembrete)
    {
        $lembrete->delete();
        return back()->with('success', 'Lembrete excluído.');
    }
}
