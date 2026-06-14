<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\ResetMovimentacaoService;
use Illuminate\Console\Command;

/**
 * Reseta a MOVIMENTAÇÃO operacional de uma oficina (tenant), preservando
 * os cadastros base (clientes, veículos, peças/estoque, serviços, funcionários,
 * fornecedores, configurações e usuários).
 *
 * SEGURANÇA:
 *  - Por padrão é DRY-RUN: apenas mostra o que seria apagado (não apaga nada).
 *  - Só apaga com a flag --confirmar.
 *  - Antes de apagar, exporta um backup JSON em storage/app/resets/.
 *  - Tudo dentro de uma transação (rollback automático em caso de erro).
 *
 * Uso:
 *   php artisan pitstop:resetar-movimentacao autofix              # dry-run (só mostra)
 *   php artisan pitstop:resetar-movimentacao autofix --confirmar  # executa (com backup)
 */
class ResetarMovimentacao extends Command
{
    protected $signature = 'pitstop:resetar-movimentacao {slug : slug da oficina (ex: autofix)} {--confirmar : Executa de fato a remoção (caso contrário é apenas simulação)}';

    protected $description = 'Zera a movimentação (orçamentos/OS/pagamentos/caixa/agendamentos) de uma oficina, mantendo os cadastros';

    public function handle(ResetMovimentacaoService $service): int
    {
        $slug   = $this->argument('slug');
        $tenant = Tenant::where('slug', $slug)->first();

        if (! $tenant) {
            $this->error("Oficina (tenant) com slug \"{$slug}\" não encontrada.");
            return self::FAILURE;
        }

        $contagem = $service->contar($tenant);
        $total    = array_sum($contagem);

        $this->newLine();
        $this->info("Oficina: {$tenant->nome}  (slug: {$tenant->slug}, id: {$tenant->id})");
        $this->newLine();
        $this->line('Movimentação que será REMOVIDA:');
        foreach ($contagem as $label => $n) {
            $this->line(sprintf('  %-22s %d', $label, $n));
        }
        $this->newLine();
        $this->comment('Cadastros PRESERVADOS: clientes, veículos, peças/estoque, serviços, funcionários, fornecedores, configurações e usuários.');
        $this->newLine();

        if ($total === 0) {
            $this->info('Não há movimentação para remover. Nada a fazer.');
            return self::SUCCESS;
        }

        if (! $this->option('confirmar')) {
            $this->warn('▶ Isto foi apenas uma SIMULAÇÃO (dry-run). NADA foi apagado.');
            $this->warn('  Para executar de fato, rode novamente com a flag --confirmar');
            return self::SUCCESS;
        }

        $resultado = $service->executar($tenant);

        $this->newLine();
        $this->info("📦 Backup salvo em: {$resultado['backup']}");
        $this->info("✅ {$resultado['total']} registros de movimentação removidos da oficina \"{$tenant->nome}\".");
        $this->info('   Cadastros (clientes, veículos, estoque, serviços) foram preservados.');

        return self::SUCCESS;
    }
}
