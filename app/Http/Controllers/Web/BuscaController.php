<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\OrdemServico;
use App\Models\Orcamento;
use App\Models\Veiculo;
use Illuminate\Http\Request;

class BuscaController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return view('pitstop.busca', ['q' => $q, 'resultados' => []]);
        }

        $like = '%' . $q . '%';

        // IMPORTANTE: agrupar os OR num where(fn) para o global scope de tenant
        // não ser anulado. Sem o agrupamento, "tenant_id = X AND a OR b" vira
        // "(tenant_id = X AND a) OR b" e vaza registros de outros tenants.
        $clientes = Cliente::where(function ($sq) use ($like) {
                $sq->where('nome', 'ilike', $like)
                   ->orWhere('telefone', 'ilike', $like)
                   ->orWhere('email', 'ilike', $like);
            })
            ->limit(10)->get();

        $veiculos = Veiculo::where(function ($sq) use ($like) {
                $sq->where('placa', 'ilike', $like)
                   ->orWhere('modelo', 'ilike', $like);
            })
            ->with('cliente')
            ->limit(10)->get();

        $ordens = OrdemServico::where('numero_os', 'ilike', $like)
            ->with(['cliente', 'veiculo'])
            ->limit(10)->get();

        $orcamentos = Orcamento::where(function ($sq) use ($q, $like) {
                $sq->where('id', is_numeric($q) ? (int) $q : 0)
                   ->orWhereHas('cliente', fn($cq) => $cq->where('nome', 'ilike', $like));
            })
            ->whereNotIn('status', ['concluido', 'cancelado'])
            ->with(['cliente', 'veiculo'])
            ->limit(10)->get();

        $resultados = [
            'clientes'   => $clientes,
            'veiculos'   => $veiculos,
            'ordens'     => $ordens,
            'orcamentos' => $orcamentos,
        ];

        return view('pitstop.busca', compact('q', 'resultados'));
    }
}
