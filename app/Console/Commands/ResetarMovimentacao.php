<?php

namespace App\Console\Commands;

use App\Models\Agendamento;
use App\Models\Caixa;
use App\Models\Comissao;
use App\Models\Financeiro;
use App\Models\Lembrete;
use App\Models\Orcamento;
use App\Models\OrcamentoInteracao;
use App\Models\OrcamentoMaoDeObra;
use App\Models\OrcamentoPeca;
use App\Models\OrcamentoServico;
use App\Models\OrdemServico;
use App\Models\OsPeca;
use App\Models\PagamentoOs;
use App\Models\PagamentoSaida;
use App\Models\ServicoFoto;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Reseta a MOVIMENTAÇÃO operacional de uma oficina (tenant), preservando
 * os cadastros base (clientes, veículos, peças/estoque, serviços, funcionários,
 * fornecedores, configurações e usuários).
 *
 * APAGA: orçamentos, ordens de serviço, pagamentos, caixa, comissões, saídas,
 *        agendamentos, lembretes, fotos de serviço e interações de orçamento.
 *
 * SEGURANÇA:
 *  - Por padrão é DRY-RUN: apenas mostra o que seria apagado (não apaga nada).
 *  - Só apaga com a flag --confirmar.
 *  - Antes de apagar, exporta um backup JSON de tudo em storage/app/resets/.
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

    public function handle(): int
    {
        $slug   = $this->argument('slug');
        $tenant = Tenant::where('slug', $slug)->first();

        if (! $tenant) {
            $this->error("Oficina (tenant) com slug \"{$slug}\" não encontrada.");
            return self::FAILURE;
        }

        $tid = $tenant->id;

        // IDs de orçamentos e OS (incluindo soft-deleted) para escopar tabelas-filhas
        $orcIds = Orcamento::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->pluck('id');
        $osIds  = OrdemServico::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->pluck('id');

        $contagem = [
            'orçamentos'          => $orcIds->count(),
            '↳ serviços'          => OrcamentoServico::whereIn('orcamento_id', $orcIds)->count(),
            '↳ peças orçadas'     => OrcamentoPeca::whereIn('orcamento_id', $orcIds)->count(),
            '↳ mão de obra'       => OrcamentoMaoDeObra::whereIn('orcamento_id', $orcIds)->count(),
            '↳ interações'        => OrcamentoInteracao::withoutGlobalScope('tenant')->where('tenant_id', $tid)->count(),
            'ordens de serviço'   => $osIds->count(),
            '↳ peças da OS'       => OsPeca::whereIn('os_id', $osIds)->count(),
            '↳ pagamentos (OS)'   => PagamentoOs::whereIn('os_id', $osIds)->count(),
            '↳ comissões'         => Comissao::withoutGlobalScope('tenant')->where('tenant_id', $tid)->count(),
            '↳ financeiro'        => Financeiro::withoutGlobalScope('tenant')->where('tenant_id', $tid)->count(),
            'fotos de serviço'    => ServicoFoto::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->count(),
            'lembretes'           => Lembrete::withoutGlobalScope('tenant')->where('tenant_id', $tid)->count(),
            'agendamentos'        => Agendamento::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->count(),
            'caixas'              => Caixa::withoutGlobalScope('tenant')->where('tenant_id', $tid)->count(),
            'saídas (despesas)'   => PagamentoSaida::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->count(),
        ];

        $total = array_sum($contagem);

        $this->newLine();
        $this->info("Oficina: {$tenant->nome}  (slug: {$tenant->slug}, id: {$tid})");
        $this->newLine();
        $this->line('Movimentação que será REMOVIDA:');
        foreach ($contagem as $label => $n) {
            $this->line(sprintf('  %-22s %d', $label, $n));
        }
        $this->newLine();
        $this->comment("Cadastros PRESERVADOS: clientes, veículos, peças/estoque, serviços, funcionários, fornecedores, configurações e usuários.");
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

        // ---------- BACKUP antes de apagar ----------
        $snapshot = [
            'gerado_em' => Carbon::now()->toIso8601String(),
            'tenant'    => ['id' => $tid, 'slug' => $tenant->slug, 'nome' => $tenant->nome],
            'dados'     => [
                'orcamentos'           => DB::table('orcamentos')->where('tenant_id', $tid)->get(),
                'orcamento_servicos'   => DB::table('orcamento_servicos')->whereIn('orcamento_id', $orcIds)->get(),
                'orcamento_pecas'      => DB::table('orcamento_pecas')->whereIn('orcamento_id', $orcIds)->get(),
                'orcamento_mao_de_obra'=> DB::table('orcamento_mao_de_obra')->whereIn('orcamento_id', $orcIds)->get(),
                'orcamento_interacoes' => DB::table('orcamento_interacoes')->where('tenant_id', $tid)->get(),
                'ordens_servico'       => DB::table('ordens_servico')->where('tenant_id', $tid)->get(),
                'os_pecas'             => DB::table('os_pecas')->whereIn('os_id', $osIds)->get(),
                'pagamentos_os'        => DB::table('pagamentos_os')->whereIn('os_id', $osIds)->get(),
                'comissoes'            => DB::table('comissoes')->where('tenant_id', $tid)->get(),
                'financeiro'           => DB::table('financeiro')->where('tenant_id', $tid)->get(),
                'servico_fotos'        => DB::table('servico_fotos')->where('tenant_id', $tid)->get(),
                'lembretes'            => DB::table('lembretes')->where('tenant_id', $tid)->get(),
                'agendamentos'         => DB::table('agendamentos')->where('tenant_id', $tid)->get(),
                'caixas'               => DB::table('caixas')->where('tenant_id', $tid)->get(),
                'pagamentos_saida'     => DB::table('pagamentos_saida')->where('tenant_id', $tid)->get(),
            ],
        ];

        $arquivo = 'resets/reset-'.$tenant->slug.'-'.Carbon::now()->format('Ymd-His').'.json';
        Storage::disk('local')->put($arquivo, json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $caminhoCompleto = Storage::disk('local')->path($arquivo);
        $this->info("📦 Backup salvo em: {$caminhoCompleto}");

        // ---------- REMOÇÃO (transação, ordem FK-safe: filhos → pais) ----------
        DB::transaction(function () use ($tid, $orcIds, $osIds) {
            // Filhos da OS
            Comissao::withoutGlobalScope('tenant')->where('tenant_id', $tid)->delete();
            Financeiro::withoutGlobalScope('tenant')->where('tenant_id', $tid)->delete();
            PagamentoOs::whereIn('os_id', $osIds)->delete();
            OsPeca::whereIn('os_id', $osIds)->delete();

            // Lembretes e fotos (referenciam OS/orçamento)
            Lembrete::withoutGlobalScope('tenant')->where('tenant_id', $tid)->delete();
            ServicoFoto::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->forceDelete();

            // Ordens de serviço
            OrdemServico::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->forceDelete();

            // Filhos do orçamento
            OrcamentoInteracao::withoutGlobalScope('tenant')->where('tenant_id', $tid)->delete();
            OrcamentoServico::whereIn('orcamento_id', $orcIds)->delete();
            OrcamentoPeca::whereIn('orcamento_id', $orcIds)->delete();
            OrcamentoMaoDeObra::whereIn('orcamento_id', $orcIds)->delete();

            // Orçamentos
            Orcamento::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->forceDelete();

            // Demais movimentações
            Agendamento::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->forceDelete();
            Caixa::withoutGlobalScope('tenant')->where('tenant_id', $tid)->delete();
            PagamentoSaida::withoutGlobalScope('tenant')->withTrashed()->where('tenant_id', $tid)->forceDelete();
        });

        $this->newLine();
        $this->info("✅ {$total} registros de movimentação removidos da oficina \"{$tenant->nome}\".");
        $this->info('   Cadastros (clientes, veículos, estoque, serviços) foram preservados.');
        $this->line('   Backup do que foi removido: '.$caminhoCompleto);

        return self::SUCCESS;
    }
}
