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
            ->orderBy('data_lembrete');

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
            'titulo'       => 'required|string|max:200',
            'cliente_id'   => 'nullable|exists:clientes,id',
            'veiculo_id'   => 'nullable|exists:veiculos,id',
            'observacao'   => 'nullable|string|max:500',
            'data_lembrete'=> 'required|date',
        ]);

        $data['status']       = 'pendente';
        $data['titulo']       = strtoupper($data['titulo']);
        $data['servico_nome'] = $data['titulo']; // mantém compatibilidade

        Lembrete::create($data);

        return back()->with('success', 'Lembrete cadastrado!');
    }

    public function update(Request $request, Lembrete $lembrete)
    {
        $request->validate(['status' => 'required|in:pendente,concluido,cancelado']);
        $lembrete->update(['status' => $request->status]);
        return back()->with('success', 'Lembrete atualizado.');
    }

    public function destroy(Lembrete $lembrete)
    {
        $lembrete->delete();
        return back()->with('success', 'Lembrete excluído.');
    }
}
