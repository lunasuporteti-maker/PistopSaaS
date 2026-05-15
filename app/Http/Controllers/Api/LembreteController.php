<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lembrete;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LembreteController extends Controller
{
    public function index(Request $request)
    {
        $query = Lembrete::with(['cliente', 'veiculo']);

        if ($request->status) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pendente');
        }

        if ($request->vencendo) {
            $query->where('data_lembrete', '<=', Carbon::today()->addDays(7));
        }

        return response()->json($query->orderBy('data_lembrete')->get());
    }

    public function update(Request $request, Lembrete $lembrete)
    {
        $request->validate([
            'status' => 'required|in:pendente,enviado,cancelado',
        ]);

        $lembrete->update(['status' => $request->status]);
        return response()->json($lembrete);
    }
}
