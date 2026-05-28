<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionLog;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookAsaasController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        // Autentica via header access_token (configurar no painel Asaas)
        $token = config('services.asaas.webhook_token');
        if ($token && $request->header('asaas-access-token') !== $token) {
            Log::warning('[Asaas Webhook] Token inválido');
            return response()->json(['ok' => false], 401);
        }

        $payload = $request->all();
        $evento  = $payload['event'] ?? null;
        $payment = $payload['payment'] ?? null;

        Log::info('[Asaas Webhook] Recebido', ['event' => $evento, 'payment_id' => $payment['id'] ?? null]);

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
            'PAYMENT_OVERDUE'                        => $this->marcarAtrasado($tenant, $payment),
            'PAYMENT_DELETED', 'PAYMENT_REFUNDED'   => $this->desativarPlano($tenant, $payment),
            default => Log::info('[Asaas Webhook] Evento ignorado', ['event' => $evento]),
        };

        return response()->json(['ok' => true]);
    }

    private function ativarPlano(Tenant $tenant, array $payment): void
    {
        $pago = isset($payment['paymentDate'])
            ? \Carbon\Carbon::parse($payment['paymentDate'])
            : now();

        $vencimento = $pago->copy()->addMonth()->toDateString();

        $tenant->update([
            'plano_ativo'    => true,
            'plano_vence_em' => $vencimento,
        ]);

        Subscription::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'plano'                    => 'padrao',
                'status'                   => 'active',
                'proximo_vencimento'       => $vencimento,
                'gateway'                  => 'asaas',
                'gateway_subscription_id'  => $payment['subscription'] ?? null,
                'gateway_customer_id'      => $payment['customer'] ?? null,
            ]
        );

        SubscriptionLog::create([
            'tenant_id'    => $tenant->id,
            'evento'       => 'payment_confirmed',
            'payload_json' => json_encode([
                'payment_id' => $payment['id'] ?? null,
                'valor'      => $payment['value'] ?? null,
                'vence_em'   => $vencimento,
            ]),
        ]);

        Log::info("[Asaas] Plano Pro ativado para {$tenant->nome} até {$vencimento}");
    }

    private function marcarAtrasado(Tenant $tenant, array $payment): void
    {
        SubscriptionLog::create([
            'tenant_id'    => $tenant->id,
            'evento'       => 'payment_overdue',
            'payload_json' => json_encode(['payment_id' => $payment['id'] ?? null]),
        ]);

        Log::warning("[Asaas] Pagamento em atraso — {$tenant->nome}");
    }

    private function desativarPlano(Tenant $tenant, array $payment): void
    {
        $tenant->update(['plano_ativo' => false, 'plano_vence_em' => null]);

        Subscription::where('tenant_id', $tenant->id)
            ->update(['status' => 'canceled']);

        SubscriptionLog::create([
            'tenant_id'    => $tenant->id,
            'evento'       => 'payment_canceled',
            'payload_json' => json_encode(['payment_id' => $payment['id'] ?? null]),
        ]);

        Log::info("[Asaas] Plano desativado — {$tenant->nome}");
    }
}
