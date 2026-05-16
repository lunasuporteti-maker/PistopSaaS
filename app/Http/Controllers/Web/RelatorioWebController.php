<?php

namespace App\Http\Controllers\Web;

use App\Exports\FinanceiroExport;
use App\Exports\FluxoCaixaExport;
use App\Exports\LucroServicosExport;
use App\Http\Controllers\Controller;
use App\Models\PagamentoOs;
use App\Models\PagamentoSaida;
use App\Models\OrcamentoServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class RelatorioWebController extends Controller
{
    public function financeiro(Request $request)
    {
        $inicio = Carbon::parse($request->inicio ?? now()->startOfMonth());
        $fim    = Carbon::parse($request->fim    ?? now()->endOfDay());

        $entradas = PagamentoOs::whereHas('ordemServico')->whereBetween('created_at', [$inicio, $fim])->sum('valor');
        $saidas   = PagamentoSaida::whereBetween('data_pagamento', [$inicio, $fim])->sum('valor');

        return view('pitstop.relatorios.financeiro', compact('entradas', 'saidas', 'inicio', 'fim'));
    }

    public function fluxoCaixa(Request $request)
    {
        $meses = (int) ($request->meses ?? 6);

        $entradas = PagamentoOs::whereHas('ordemServico')
            ->select(DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mes"), DB::raw('SUM(valor) as total'))
            ->where('created_at', '>=', now()->subMonths($meses))
            ->groupBy('mes')->orderBy('mes')->pluck('total', 'mes');

        $saidas = PagamentoSaida::select(DB::raw("TO_CHAR(data_pagamento, 'YYYY-MM') as mes"), DB::raw('SUM(valor) as total'))
            ->where('data_pagamento', '>=', now()->subMonths($meses))
            ->groupBy('mes')->orderBy('mes')->pluck('total', 'mes');

        return view('pitstop.relatorios.fluxo-caixa', compact('entradas', 'saidas', 'meses'));
    }

    public function lucroServico(Request $request)
    {
        $inicio = Carbon::parse($request->inicio ?? now()->startOfMonth());
        $fim    = Carbon::parse($request->fim    ?? now()->endOfDay());

        $servicos = OrcamentoServico::select('servico_nome', DB::raw('COUNT(*) as quantidade'), DB::raw('SUM(valor) as total'))
            ->whereHas('orcamento', fn($q) => $q->where('status', 'concluido')->whereBetween('concluido_em', [$inicio, $fim]))
            ->groupBy('servico_nome')->orderByDesc('total')->get();

        return view('pitstop.relatorios.lucro-servico', compact('servicos', 'inicio', 'fim'));
    }

    public function exportFinanceiro(Request $request)
    {
        $inicio = Carbon::parse($request->inicio ?? now()->startOfMonth());
        $fim    = Carbon::parse($request->fim    ?? now()->endOfDay());

        $entradas = (float) PagamentoOs::whereHas('ordemServico')->whereBetween('created_at', [$inicio, $fim])->sum('valor');
        $saidas   = (float) PagamentoSaida::whereBetween('data_pagamento', [$inicio, $fim])->sum('valor');

        return Excel::download(new FinanceiroExport($entradas, $saidas, $inicio, $fim),
            'financeiro-' . $inicio->format('Y-m-d') . '-' . $fim->format('Y-m-d') . '.xlsx');
    }

    public function exportFluxoCaixa(Request $request)
    {
        $meses = (int) ($request->meses ?? 6);
        return Excel::download(new FluxoCaixaExport($meses), "fluxo-caixa-{$meses}meses.xlsx");
    }

    public function exportLucroServico(Request $request)
    {
        $inicio = Carbon::parse($request->inicio ?? now()->startOfMonth());
        $fim    = Carbon::parse($request->fim    ?? now()->endOfDay());
        return Excel::download(new LucroServicosExport($inicio, $fim),
            'receita-servicos-' . $inicio->format('Y-m-d') . '-' . $fim->format('Y-m-d') . '.xlsx');
    }
}
