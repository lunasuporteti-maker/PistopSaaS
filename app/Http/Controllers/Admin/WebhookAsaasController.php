<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookAsaasController extends Controller
{
    /**
     * Webhook recebido do Asaas quando um pagamento é confirmado.
     *
     * Asaas envia eventos via POST com JSON.
     * Configurar no painel Asaas: https://www.asaas.com/dashboard/config/integracoes/webhook
     *
     * Eventos tratados:
     *  - PAYMENT_CONFIRMED / PAYMENT_RECEIVED → ativa plano
     *  - PAYMENT_OVERDUE   / PAYMENT_DELETED  → desativa plano (opcional)
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $evento  = $payload['event'] ?? null;
        $payment = $payload['payment'] ?? null;

        Log::info('[Asaas Webhook] Recebido', ['event' => $evento, 'payment_id' => $payment['id'] ?? null]);

        // Identifica o tenant pelo campo externalReference do pagamento
        // Ao criar o pagamento no Asaas, passar: externalReference = "tenant:{slug}"
        $ref = $payment['externalReference'] ?? null;

        if (! $ref || ! str_starts_with($ref, 'tenant:')) {
            return response()->json(['ok' => true, 'msg' => 'Sem referência de tenant']);
        }

        $slug   = substr($ref, strlen('tenant:'));
        $tenant = Tenant::where('slug', $slug)->first();

        if (! $tenant) {
            Log::warning('[Asaas Webhook] Tenant não encontrado', ['slug' => $slug]);
            return response()->json(['ok' => false, 'msg' => 'Tenant não encontrado'], 404);
        }

        match ($evento) {
            'PAYMENT_CONFIRMED', 'PAYMENT_RECEIVED' => $this->ativarPlano($tenant, $payment),
            'PAYMENT_OVERDUE'                        => $this->marcarAtrasado($tenant),
            'PAYMENT_DELETED', 'PAYMENT_REFUNDED'   => $this->desativarPlano($tenant),
            default                                  => Log::info('[Asaas Webhook] Evento ignorado', ['event' => $evento]),
        };

        return response()->json(['ok' => true]);
    }

    private function ativarPlano(Tenant $tenant, array $payment): void
    {
        // Calcula vencimento: 1 mês após data de pagamento ou dueDate
        $pago = isset($payment['paymentDate'])
            ? \Carbon\Carbon::parse($payment['paymentDate'])
            : now();

        $tenant->update([
            'plano_ativo'    => true,
            'plano_vence_em' => $pago->addMonth()->toDateString(),
        ]);

        Log::info("[Asaas Webhook] Plano ativado para {$tenant->nome} até {$tenant->plano_vence_em}");
    }

    private function marcarAtrasado(Tenant $tenant): void
    {
        // Por enquanto apenas loga — não desativa imediatamente
        Log::warning("[Asaas Webhook] Pagamento em atraso para {$tenant->nome}");
    }

    private function desativarPlano(Tenant $tenant): void
    {
        $tenant->update([
            'plano_ativo'    => false,
            'plano_vence_em' => null,
        ]);

        Log::info("[Asaas Webhook] Plano desativado para {$tenant->nome}");
    }
}
