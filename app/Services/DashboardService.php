<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Veiculo;
use App\Models\Orcamento;
use App\Models\OrdemServico;
use App\Models\Peca;
use App\Models\Agendamento;
use App\Models\PagamentoOs;
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

        return [
            'total_clientes'      => Cliente::count(),
            'total_veiculos'      => Veiculo::count(),
            'orcamentos_mes'      => Orcamento::whereMonth('created_at', $hoje->month)
                                        ->whereYear('created_at', $hoje->year)->count(),
            'estoque_baixo_count' => Peca::whereColumn('quantidade', '<=', 'estoque_minimo')->count(),
            'lembretes_pendentes' => Lembrete::where('status', 'pendente')
                                        ->where('data_lembrete', '<=', Carbon::today())->count(),
            'receita_hoje'        => (float) PagamentoOs::whereDate('created_at', $hoje)->sum('valor'),
            'receita_mes'         => (float) PagamentoOs::whereBetween('created_at', [$inicioMes, $fimDia])->sum('valor'),
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
