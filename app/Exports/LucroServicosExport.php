<?php

namespace App\Exports;

use App\Models\OrcamentoServico;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class LucroServicosExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        private Carbon $inicio,
        private Carbon $fim
    ) {}

    public function collection()
    {
        return OrcamentoServico::whereHas('orcamento', fn($q) =>
            $q->where('status', 'concluido')->whereBetween('concluido_em', [$this->inicio, $this->fim])
        )
        ->select('servico_nome', DB::raw('COUNT(*) as quantidade'), DB::raw('SUM(valor) as total'))
        ->groupBy('servico_nome')
        ->orderByDesc('total')
        ->get()
        ->map(fn($s) => [
            'Serviço'       => $s->servico_nome,
            'Quantidade'    => $s->quantidade,
            'Receita Total' => number_format($s->total, 2, ',', '.'),
            'Ticket Médio'  => number_format($s->total / $s->quantidade, 2, ',', '.'),
        ]);
    }

    public function headings(): array
    {
        return ['Serviço', 'Quantidade', 'Receita Total (R$)', 'Ticket Médio (R$)'];
    }

    public function title(): string
    {
        return 'Receita por Serviço';
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function columnWidths(): array
    {
        return ['A' => 40, 'B' => 15, 'C' => 22, 'D' => 22];
    }
}
