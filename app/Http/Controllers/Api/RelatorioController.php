<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PagamentoOs;
use App\Models\PagamentoSaida;
use App\Models\OrdemServico;
use App\Models\OrcamentoServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RelatorioController extends Controller
{
    public function financeiro(Request $request)
    {
        $inicio = $request->inicio ? Carbon::parse($request->inicio) : Carbon::now()->startOfMonth();
        $fim    = $request->fim    ? Carbon::parse($request->fim)    : Carbon::now()->endOfDay();

        $entradas = PagamentoOs::whereBetween('created_at', [$inicio, $fim])->sum('valor');
        $saidas   = PagamentoSaida::whereBetween('data_pagamento', [$inicio, $fim])->sum('valor');

        return response()->json([
            'periodo' => ['inicio' => $inicio->toDateString(), 'fim' => $fim->toDateString()],
            'entradas' => (float) $entradas,
            'saidas'   => (float) $saidas,
            'lucro'    => (float) ($entradas - $saidas),
        ]);
    }

    public function fluxoCaixa(Request $request)
    {
        $meses = (int) ($request->meses ?? 6);

        // TO_CHAR é compatível com PostgreSQL; para MySQL usar DATE_FORMAT
        $entradas = PagamentoOs::select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mes"),
                DB::raw('SUM(valor) as total')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths($meses))
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        $saidas = PagamentoSaida::select(
                DB::raw("TO_CHAR(data_pagamento, 'YYYY-MM') as mes"),
                DB::raw('SUM(valor) as total')
            )
            ->where('data_pagamento', '>=', Carbon::now()->subMonths($meses))
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        return response()->json(['entradas' => $entradas, 'saidas' => $saidas]);
    }

    public function lucroServico(Request $request)
    {
        $inicio = $request->inicio ? Carbon::parse($request->inicio) : Carbon::now()->startOfMonth();
        $fim    = $request->fim    ? Carbon::parse($request->fim)    : Carbon::now()->endOfDay();

        $servicos = OrcamentoServico::select(
                'servico_nome',
                DB::raw('COUNT(*) as quantidade'),
                DB::raw('SUM(valor) as total')
            )
            ->whereHas('orcamento', function ($q) use ($inicio, $fim) {
                $q->where('status', 'concluido')
                  ->whereBetween('concluido_em', [$inicio, $fim]);
            })
            ->groupBy('servico_nome')
            ->orderByDesc('total')
            ->get();

        return response()->json($servicos);
    }

    public function saidasCategoria(Request $request)
    {
        $inicio = $request->inicio ? Carbon::parse($request->inicio) : Carbon::now()->startOfMonth();
        $fim    = $request->fim    ? Carbon::parse($request->fim)    : Carbon::now()->endOfDay();

        $categorias = PagamentoSaida::select(
                'categoria',
                DB::raw('SUM(valor) as total')
            )
            ->whereBetween('data_pagamento', [$inicio, $fim])
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->get();

        return response()->json($categorias);
    }

    public function detalhado(Request $request)
    {
        $inicio = $request->inicio ? Carbon::parse($request->inicio) : Carbon::now()->startOfMonth();
        $fim    = $request->fim    ? Carbon::parse($request->fim)    : Carbon::now()->endOfDay();

        $os = OrdemServico::with(['cliente', 'veiculo', 'pagamentos'])
            ->whereNotNull('finalizado_em')
            ->whereBetween('finalizado_em', [$inicio, $fim])
            ->orderBy('finalizado_em', 'desc')
            ->get();

        return response()->json($os);
    }
}
