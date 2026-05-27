<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\EntradaEstoque;
use App\Models\Orcamento;
use App\Models\OrdemServico;
use App\Models\PagamentoOs;
use App\Models\Peca;
use App\Models\Veiculo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hoje              = Carbon::today();
        $inicioMes         = $hoje->copy()->startOfMonth();
        $inicioMesAnterior = $inicioMes->copy()->subMonth();
        $fimMesAnterior    = $inicioMes->copy()->subDay()->endOfDay();

        $receitaHoje = PagamentoOs::whereDate('created_at', $hoje)->sum('valor');
        $receitaMes  = PagamentoOs::whereBetween('created_at', [$inicioMes, $hoje->copy()->endOfDay()])->sum('valor');

        // Compras do mês (entradas de estoque ativas)
        $comprasMes = EntradaEstoque::ativas()
            ->whereBetween('data_entrada', [$inicioMes, $hoje->copy()->endOfDay()])
            ->sum('valor_total');

        $comprasMesAnterior = EntradaEstoque::ativas()
            ->whereBetween('data_entrada', [$inicioMesAnterior, $fimMesAnterior])
            ->sum('valor_total');

        $variacaoCompras = $comprasMesAnterior > 0
            ? round((($comprasMes - $comprasMesAnterior) / $comprasMesAnterior) * 100, 1)
            : null;

        $statusOrcamentos = Orcamento::select('status', DB::raw('count(*) as total'))
            ->whereMonth('created_at', $hoje->month)
            ->groupBy('status')
            ->pluck('total', 'status');

        $fila = Orcamento::with(['cliente', 'veiculo'])
            ->whereIn('status', ['aprovado', 'em_servico'])
            ->orderBy('posicao_fila')
            ->get();

        $estoqueBaixo = Peca::whereColumn('quantidade', '<=', 'estoque_minimo')->get();

        $agendamentosHoje = Agendamento::with(['cliente', 'veiculo'])
            ->whereDate('data_hora', $hoje)
            ->orderBy('data_hora')
            ->get();

        $ultimasOs = OrdemServico::with(['cliente', 'veiculo'])
            ->whereNotNull('finalizado_em')
            ->orderBy('finalizado_em', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'total_clientes'       => Cliente::count(),
            'total_veiculos'       => Veiculo::count(),
            'orcamentos_mes'       => Orcamento::whereMonth('created_at', $hoje->month)->count(),
            'estoque_baixo_count'  => $estoqueBaixo->count(),
            'receita_hoje'         => (float) $receitaHoje,
            'receita_mes'          => (float) $receitaMes,
            'compras_mes'          => [
                'valor_total'         => (float) $comprasMes,
                'valor_mes_anterior'  => (float) $comprasMesAnterior,
                'variacao_percentual' => $variacaoCompras,
            ],
            'fila_servico'         => $fila,
            'status_orcamentos'    => $statusOrcamentos,
            'agendamentos_hoje'    => $agendamentosHoje,
            'ultimas_os'           => $ultimasOs,
            'estoque_baixo'        => $estoqueBaixo,
        ]);
    }
}
