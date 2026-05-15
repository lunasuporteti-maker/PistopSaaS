<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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

        return view('pitstop.lembretes.index', compact('lembretes'));
    }

    public function update(Request $request, Lembrete $lembrete)
    {
        $request->validate(['status' => 'required|in:pendente,enviado,cancelado']);
        $lembrete->update(['status' => $request->status]);
        return back()->with('success', 'Lembrete atualizado.');
    }
}
