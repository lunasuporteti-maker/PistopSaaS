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

/**
 * Notifica admins/gerentes do tenant quando um orçamento é aprovado no portal público.
 * Story 2.2 (AC5 / FR-030, FR-031).
 */
class NotificarAprovacaoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $orcamentoId) {}

    public function handle(): void
    {
        // Job roda fora do contexto HTTP — sem tenant no IoC. Bypass do global scope.
        $orcamento = Orcamento::withoutGlobalScope('tenant')
            ->with(['cliente', 'veiculo'])
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

        $cliente  = $orcamento->cliente?->nome ?? 'Cliente';
        $veiculo  = trim(($orcamento->veiculo?->marca ?? '') . ' ' . ($orcamento->veiculo?->modelo ?? ''));
        $valor    = number_format((float) $orcamento->valor_total, 2, ',', '.');
        $linkOrc  = url('/orcamentos/' . $orcamento->id);

        $corpo = "Orçamento aprovado pelo cliente no portal!\n\n"
            . "Cliente: {$cliente}\n"
            . "Veículo: {$veiculo}\n"
            . "Orçamento: #{$orcamento->id}\n"
            . "Valor: R$ {$valor}\n\n"
            . "A OS foi gerada automaticamente.\n"
            . "Ver no sistema: {$linkOrc}";

        try {
            Mail::raw($corpo, function ($m) use ($destinatarios, $orcamento) {
                $m->to($destinatarios)
                    ->subject("Orçamento #{$orcamento->id} aprovado no portal");
            });
        } catch (\Throwable $e) {
            Log::error('[NotificarAprovacao] Erro ao enviar email: ' . $e->getMessage());
        }
    }
}
