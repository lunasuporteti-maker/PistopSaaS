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

        $clientes = Cliente::where('nome', 'ilike', $like)
            ->orWhere('telefone', 'ilike', $like)
            ->orWhere('email', 'ilike', $like)
            ->limit(10)->get();

        $veiculos = Veiculo::where('placa', 'ilike', $like)
            ->orWhere('modelo', 'ilike', $like)
            ->with('cliente')
            ->limit(10)->get();

        $ordens = OrdemServico::where('numero_os', 'ilike', $like)
            ->with(['cliente', 'veiculo'])
            ->limit(10)->get();

        $orcamentos = Orcamento::where('id', is_numeric($q) ? (int) $q : 0)
            ->orWhereHas('cliente', fn($sq) => $sq->where('nome', 'ilike', $like))
            ->with(['cliente', 'veiculo'])
            ->whereNotIn('status', ['concluido', 'cancelado'])
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
