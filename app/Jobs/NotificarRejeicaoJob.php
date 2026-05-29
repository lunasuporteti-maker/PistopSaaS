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
use Illuminate\Support\Str;

/**
 * Notifica admins/gerentes do tenant quando um cliente solicita revisão no portal.
 * Story 2.3 (AC5 / FR-031).
 */
class NotificarRejeicaoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $orcamentoId,
        public readonly string $motivo,
    ) {}

    public function handle(): void
    {
        $orcamento = Orcamento::withoutGlobalScope('tenant')
            ->with(['cliente'])
            ->find($this->orcamentoId);

        if (! $orcamento) {
            return;
        }

        $destinatarios = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $orcamento->tenant_id)
            ->whereIn('perfil', ['admin', 'gerente'])
            ->where('ativo', true)
            ->pluck('email')
            ->filter()
            ->all();

        if (empty($destinatarios)) {
            return;
        }

        $cliente        = $orcamento->cliente?->nome ?? 'Cliente';
        $motivoTruncado = Str::limit($this->motivo, 100);
        $linkOrc        = url('/orcamentos/' . $orcamento->id);

        $corpo = "{$cliente} solicitou revisão no orçamento #{$orcamento->id}:\n\n"
            . "\"{$motivoTruncado}\"\n\n"
            . "O orçamento permanece aguardando aprovação. Ajuste e reenvie se necessário.\n"
            . "Ver no sistema: {$linkOrc}";

        try {
            Mail::raw($corpo, function ($m) use ($destinatarios, $orcamento) {
                $m->to($destinatarios)
                    ->subject("Revisão solicitada — Orçamento #{$orcamento->id}");
            });
        } catch (\Throwable $e) {
            Log::error('[NotificarRejeicao] Erro ao enviar email: ' . $e->getMessage());
        }
    }
}
