<?php

namespace App\Jobs;

use App\Models\Orcamento;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificarRevisaoValorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $orcamentoId,
        public readonly float $valorAntes,
        public readonly float $valorNovo,
    ) {}

    public function handle(): void
    {
        $orcamento = Orcamento::withoutGlobalScope('tenant')->find($this->orcamentoId);
        if (! $orcamento) {
            return;
        }

        $destinatarios = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $orcamento->tenant_id)
            ->whereIn('perfil', ['admin', 'gerente'])
            ->where('ativo', true)
            ->pluck('email')
            ->filter()
            ->toArray();

        if (empty($destinatarios)) {
            return;
        }

        $antes = 'R$ ' . number_format($this->valorAntes, 2, ',', '.');
        $novo  = 'R$ ' . number_format($this->valorNovo, 2, ',', '.');

        try {
            Mail::raw(
                "O valor do orçamento #{$orcamento->id} foi alterado de {$antes} para {$novo}.\n\n"
                . "O cliente precisará re-aprovar o orçamento pelo portal público.",
                fn ($m) => $m->to($destinatarios)
                             ->subject("Valor alterado — Orçamento #{$orcamento->id} requer nova aprovação")
            );
        } catch (\Throwable $e) {
            Log::error('[NotificarRevisaoValor] ' . $e->getMessage());
        }
    }
}
