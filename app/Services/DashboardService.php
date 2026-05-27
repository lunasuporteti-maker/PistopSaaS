<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Veiculo;
use App\Models\Orcamento;
use App\Models\OrdemServico;
use App\Models\Peca;
use App\Models\Agendamento;
use App\Models\PagamentoOs;
use App\Models\PagamentoSaida;
use App\Models\Lembrete;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    public function resumo(): array
    {
        $hoje      = Carbon::today();
        $inicioMes = $hoje->copy()->startOfMonth();
        $fimDia    = $hoje->copy()->endOfDay();

        // Ticket médio — receita total do mês / nº de OS finalizadas no mês
        $totalOsMes = OrdemServico::whereMonth('finalizado_em', $hoje->month)
            ->whereYear('finalizado_em', $hoje->year)
            ->count();

        $ticketMedio = $totalOsMes > 0
            ? (float) PagamentoOs::whereHas('ordemServico', fn ($q) =>
                $q->whereMonth('finalizado_em', $hoje->month)
                  ->whereYear('finalizado_em', $hoje->year)
              )->sum('valor') / $totalOsMes
            : 0;

        // Taxa de conversão orçamento → OS no mês
        $orcamentosMes = Orcamento::whereMonth('created_at', $hoje->month)
            ->whereYear('created_at', $hoje->year)
            ->count();

        $taxaConversao = $orcamentosMes > 0
            ? round(($totalOsMes / $orcamentosMes) * 100, 1)
            : 0;

        // Tempo médio de serviço (aprovado_em → concluido_em) em horas
        // PostgreSQL: EXTRACT(EPOCH FROM ...) | SQLite: strftime('%s', ...)
        $isPostgres = DB::connection()->getDriverName() === 'pgsql';
        $rawExpr = $isPostgres
            ? 'AVG(EXTRACT(EPOCH FROM (concluido_em - aprovado_em)) / 3600) as horas_media'
            : "AVG((strftime('%s', concluido_em) - strftime('%s', aprovado_em)) / 3600.0) as horas_media";

        $tempoMedio = Orcamento::whereNotNull('aprovado_em')
            ->whereNotNull('concluido_em')
            ->whereMonth('concluido_em', $hoje->month)
            ->whereYear('concluido_em', $hoje->year)
            ->selectRaw($rawExpr)
            ->value('horas_media');

        $tempoMedioHoras = $tempoMedio ? round((float) $tempoMedio, 1) : 0;

        return [
            'total_clientes'      => Cliente::count(),
            'total_veiculos'      => Veiculo::count(),
            'orcamentos_mes'      => $orcamentosMes,
            'estoque_baixo_count' => Peca::whereColumn('quantidade', '<=', 'estoque_minimo')->count(),
            'lembretes_pendentes' => Lembrete::where('status', 'pendente')
                                        ->where('data_lembrete', '<=', Carbon::today())->count(),
            'receita_hoje'        => (float) PagamentoOs::whereDate('created_at', $hoje)->sum('valor'),
            'receita_mes'         => (float) PagamentoOs::whereBetween('created_at', [$inicioMes, $fimDia])->sum('valor'),
            'ticket_medio'        => $ticketMedio,
            'taxa_conversao'      => $taxaConversao,
            'tempo_medio_horas'   => $tempoMedioHoras,
        ];
    }

    public function fila(): array
    {
        return Orcamento::with(['cliente', 'veiculo'])
            ->whereIn('status', ['aprovado', 'em_servico'])
            ->orderBy('posicao_fila')
            ->get()
            ->toArray();
    }

    public function graficosStatus(): array
    {
        return Orcamento::select('status', DB::raw('count(*) as total'))
            ->whereMonth('created_at', Carbon::now()->month)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }
}
