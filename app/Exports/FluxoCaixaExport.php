<?php

namespace App\Exports;

use App\Models\PagamentoOs;
use App\Models\PagamentoSaida;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class FluxoCaixaExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private int $meses) {}

    public function collection(): Collection
    {
        $entradas = PagamentoOs::whereHas('ordemServico')
            ->select(DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mes"), DB::raw('SUM(valor) as total'))
            ->where('created_at', '>=', now()->subMonths($this->meses))
            ->groupBy('mes')->orderBy('mes')->pluck('total', 'mes');

        $saidas = PagamentoSaida::select(DB::raw("TO_CHAR(data_pagamento, 'YYYY-MM') as mes"), DB::raw('SUM(valor) as total'))
            ->where('data_pagamento', '>=', now()->subMonths($this->meses))
            ->groupBy('mes')->orderBy('mes')->pluck('total', 'mes');

        $mesesLista = collect($entradas->keys()->merge($saidas->keys())->unique()->sort()->values());

        return $mesesLista->map(function ($mes) use ($entradas, $saidas) {
            $e     = (float) ($entradas[$mes] ?? 0);
            $s     = (float) ($saidas[$mes] ?? 0);
            $saldo = $e - $s;
            return [
                'Mês'      => Carbon::createFromFormat('Y-m', $mes)->format('m/Y'),
                'Entradas' => number_format($e, 2, ',', '.'),
                'Saídas'   => number_format($s, 2, ',', '.'),
                'Saldo'    => number_format($saldo, 2, ',', '.'),
            ];
        });
    }

    public function headings(): array
    {
        return ['Mês', 'Entradas (R$)', 'Saídas (R$)', 'Saldo (R$)'];
    }

    public function title(): string
    {
        return 'Fluxo de Caixa';
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function columnWidths(): array
    {
        return ['A' => 12, 'B' => 18, 'C' => 18, 'D' => 18];
    }
}
