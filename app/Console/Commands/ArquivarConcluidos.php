<?php

namespace App\Console\Commands;

use App\Models\Orcamento;
use Illuminate\Console\Command;

class ArquivarConcluidos extends Command
{
    protected $signature   = 'pitstop:arquivar-concluidos';
    protected $description = 'Arquiva automaticamente orçamentos concluídos há mais de 48 horas';

    public function handle(): int
    {
        $total = Orcamento::withoutGlobalScope('tenant')
            ->where('status', 'concluido')
            ->whereNull('arquivado_em')
            ->where('concluido_em', '<=', now()->subHours(48))
            ->update(['arquivado_em' => now()]);

        if ($total > 0) {
            $this->info("Arquivados: {$total} orçamento(s) concluído(s) há mais de 48h.");
        }

        return Command::SUCCESS;
    }
}
