<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EntradaEstoque;
use App\Models\EntradaEstoqueItem;
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

    /**
     * GET /api/relatorios/compras
     * Relatório financeiro de compras (entradas de estoque ativas).
     * Acessível apenas por admin e gerente.
     */
    public function compras(Request $request)
    {
        abort_if(auth()->user()->perfil === 'operador', 403, 'Acesso restrito.');

        $inicio = $request->inicio
            ? Carbon::parse($request->inicio)->startOfDay()
            : Carbon::now()->startOfMonth();
        $fim = $request->fim
            ? Carbon::parse($request->fim)->endOfDay()
            : Carbon::now()->endOfDay();

        $baseQuery = EntradaEstoque::ativas()
            ->whereBetween('data_entrada', [$inicio, $fim]);

        $valorTotal    = $baseQuery->sum('valor_total');
        $totalEntradas = $baseQuery->count();

        $totalItens = EntradaEstoqueItem::whereHas('entrada', function ($q) use ($inicio, $fim) {
            $q->ativas()->whereBetween('data_entrada', [$inicio, $fim]);
        })->sum('quantidade');

        $porFornecedor = EntradaEstoque::ativas()
            ->with('fornecedor:id,nome')
            ->whereBetween('data_entrada', [$inicio, $fim])
            ->select('fornecedor_id', DB::raw('SUM(valor_total) as total'), DB::raw('COUNT(*) as qtd_entradas'))
            ->groupBy('fornecedor_id')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'periodo'        => ['inicio' => $inicio->toDateString(), 'fim' => $fim->toDateString()],
            'valor_total'    => (float) $valorTotal,
            'total_entradas' => (int) $totalEntradas,
            'total_itens'    => (int) $totalItens,
            'por_fornecedor' => $porFornecedor,
        ]);
    }

    /**
     * GET /api/relatorios/compras/exportar
     * Exporta entradas de estoque do período em CSV.
     * Acessível apenas por admin e gerente.
     */
    public function exportarCompras(Request $request)
    {
        abort_if(auth()->user()->perfil === 'operador', 403, 'Acesso restrito.');

        $inicio = $request->inicio
            ? Carbon::parse($request->inicio)->startOfDay()
            : Carbon::now()->startOfMonth();
        $fim = $request->fim
            ? Carbon::parse($request->fim)->endOfDay()
            : Carbon::now()->endOfDay();

        $entradas = EntradaEstoque::with(['fornecedor:id,nome', 'usuario:id,name'])
            ->ativas()
            ->whereBetween('data_entrada', [$inicio, $fim])
            ->orderBy('data_entrada', 'desc')
            ->get();

        $linhas   = [];
        $linhas[] = implode(',', [
            'Número', 'Data', 'Fornecedor', 'Responsável',
            'Valor Total', 'Status', 'Observações',
        ]);

        foreach ($entradas as $entrada) {
            $linhas[] = implode(',', [
                '"' . $entrada->numero_entrada . '"',
                '"' . ($entrada->data_entrada ? $entrada->data_entrada->format('d/m/Y') : '') . '"',
                '"' . ($entrada->fornecedor?->nome ?? '') . '"',
                '"' . ($entrada->usuario?->name ?? '') . '"',
                number_format((float) $entrada->valor_total, 2, ',', '.'),
                '"' . $entrada->status . '"',
                '"' . str_replace('"', '""', $entrada->observacoes ?? '') . '"',
            ]);
        }

        $conteudo = implode("\n", $linhas);
        $nomeArquivo = 'compras_' . $inicio->format('Y-m-d') . '_' . $fim->format('Y-m-d') . '.csv';

        return response($conteudo, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nomeArquivo . '"',
        ]);
    }
}
