<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class FinanceiroExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        private float $entradas,
        private float $saidas,
        private Carbon $inicio,
        private Carbon $fim
    ) {}

    public function collection(): Collection
    {
        $lucro = $this->entradas - $this->saidas;
        return collect([
            ['Período'  => $this->inicio->format('d/m/Y') . ' a ' . $this->fim->format('d/m/Y'),
             'Entradas' => number_format($this->entradas, 2, ',', '.'),
             'Saídas'   => number_format($this->saidas, 2, ',', '.'),
             'Resultado'=> number_format($lucro, 2, ',', '.')],
        ]);
    }

    public function headings(): array
    {
        return ['Período', 'Entradas (R$)', 'Saídas (R$)', 'Resultado (R$)'];
    }

    public function title(): string
    {
        return 'Financeiro';
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 18, 'C' => 18, 'D' => 18];
    }
}
