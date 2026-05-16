<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Veiculo;
use App\Models\Orcamento;
use App\Models\OrdemServico;
use App\Models\Peca;
use App\Models\Agendamento;
use App\Models\PagamentoOs;
use App\Models\PagamentoSaida;
use App\Models\OrcamentoServico;
use App\Models\Lembrete;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $hoje      = Carbon::today();
        $inicioMes = $hoje->copy()->startOfMonth();

        // ── KPIs ───────────────────────────────────────────────────────
        $receitaHoje = PagamentoOs::whereHas('ordemServico')->whereDate('created_at', $hoje)->sum('valor');
        $receitaMes  = PagamentoOs::whereHas('ordemServico')->where('created_at', '>=', $inicioMes)->sum('valor');
        $saidasMes   = PagamentoSaida::where('data_pagamento', '>=', $inicioMes)->sum('valor');

        // ── Fila / Agendamentos / últimas OS ───────────────────────────
        $fila = Orcamento::with(['cliente', 'veiculo'])
            ->whereIn('status', ['aprovado', 'em_servico'])
            ->orderBy('posicao_fila')->get();

        $agendamentosHoje = Agendamento::with(['cliente', 'veiculo'])
            ->whereDate('data_hora', $hoje)->orderBy('data_hora')->get();

        $ultimasOs = OrdemServico::with(['cliente', 'veiculo'])
            ->whereNotNull('finalizado_em')
            ->orderBy('finalizado_em', 'desc')->limit(5)->get();

        $estoqueBaixo = Peca::whereColumn('quantidade', '<=', 'estoque_minimo')->get();

        // ── Dados para gráficos ────────────────────────────────────────

        // 1. Receita x Saída — últimos 6 meses
        $meses = collect();
        for ($i = 5; $i >= 0; $i--) {
            $m = $hoje->copy()->subMonths($i);
            $meses->push([
                'label'   => $m->translatedFormat('M/y'),
                'inicio'  => $m->copy()->startOfMonth(),
                'fim'     => $m->copy()->endOfMonth(),
            ]);
        }

        $chartReceitaLabels  = [];
        $chartReceitaEntrada = [];
        $chartReceitaSaida   = [];
        foreach ($meses as $m) {
            $chartReceitaLabels[]  = $m['label'];
            $chartReceitaEntrada[] = round(PagamentoOs::whereHas('ordemServico')->whereBetween('created_at', [$m['inicio'], $m['fim']])->sum('valor'), 2);
            $chartReceitaSaida[]   = round(PagamentoSaida::whereBetween('data_pagamento', [$m['inicio'], $m['fim']])->sum('valor'), 2);
        }

        // 2. Status dos orçamentos — mês atual
        $statusOrcamentos = Orcamento::select('status', DB::raw('count(*) as total'))
            ->whereMonth('created_at', $hoje->month)->whereYear('created_at', $hoje->year)
            ->groupBy('status')->pluck('total', 'status');

        $chartStatusLabels = ['Orçamento', 'Aprovado', 'Em Serviço', 'Concluído', 'Cancelado'];
        $chartStatusData   = [
            $statusOrcamentos['orcamento']  ?? 0,
            $statusOrcamentos['aprovado']   ?? 0,
            $statusOrcamentos['em_servico'] ?? 0,
            $statusOrcamentos['concluido']  ?? 0,
            $statusOrcamentos['cancelado']  ?? 0,
        ];

        // 3. Top 5 serviços do mês
        $topServicos = OrcamentoServico::select('servico_nome', DB::raw('count(*) as total'))
            ->whereHas('orcamento', fn($q) => $q->whereMonth('created_at', $hoje->month))
            ->groupBy('servico_nome')->orderByDesc('total')->limit(5)->get();

        $chartServicosLabels = $topServicos->pluck('servico_nome')->toArray();
        $chartServicosData   = $topServicos->pluck('total')->toArray();

        return view('pitstop.dashboard', compact(
            'receitaHoje', 'receitaMes', 'saidasMes',
            'fila', 'agendamentosHoje', 'ultimasOs', 'estoqueBaixo',
            'statusOrcamentos',
            'chartReceitaLabels', 'chartReceitaEntrada', 'chartReceitaSaida',
            'chartStatusLabels',  'chartStatusData',
            'chartServicosLabels','chartServicosData',
        ));
    }
}
