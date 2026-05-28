<?php

namespace App\Console\Commands;

use App\Models\OrdemServico;
use Illuminate\Console\Command;

class ArquivarConcluidos extends Command
{
    protected $signature   = 'pitstop:arquivar-concluidos';
    protected $description = 'Arquiva automaticamente OSs concluídas há mais de 48 horas';

    public function handle(): int
    {
        $total = OrdemServico::withoutGlobalScope('tenant')
            ->where('status', 'concluido')
            ->whereNull('arquivado_em')
            ->where('concluido_em', '<=', now()->subHours(48))
            ->update(['arquivado_em' => now()]);

        if ($total > 0) {
            $this->info("Arquivadas: {$total} OS(s) concluída(s) há mais de 48h.");
        }

        return Command::SUCCESS;
    }
}
