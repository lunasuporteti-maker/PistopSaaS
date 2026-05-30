<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    private string $baseUrl;

    private string $apiKey;

    public function __construct()
    {
        $env = config('services.asaas.environment', 'sandbox');
        $this->baseUrl = $env === 'production'
            ? 'https://api.asaas.com/v3'
            : 'https://sandbox.asaas.com/api/v3';
        $this->apiKey = config('services.asaas.api_key', '');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    public function createOrFetchCustomer(Tenant $tenant, User $adminUser): ?string
    {
        // Verifica se já temos customer_id na subscription
        $subscription = $tenant->subscription;
        if ($subscription?->gateway_customer_id) {
            return $subscription->gateway_customer_id;
        }

        try {
            $response = Http::withHeaders(['access_token' => $this->apiKey])
                ->post("{$this->baseUrl}/customers", [
                    'name' => $adminUser->name ?? $tenant->nome,
                    'email' => $adminUser->email,
                    'externalReference' => "tenant:{$tenant->slug}",
                ]);

            if ($response->successful()) {
                $customerId = $response->json('id');
                // Salva na subscription se existir
                $tenant->subscription?->update(['gateway_customer_id' => $customerId]);

                return $customerId;
            }

            Log::warning('[Asaas] Falha ao criar customer', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Throwable $e) {
            Log::error('[Asaas] Erro ao criar customer: '.$e->getMessage());
        }

        return null;
    }

    public function createCheckoutUrl(Tenant $tenant, User $adminUser): ?string
    {
        if (! $this->isConfigured()) {
            return $this->fallbackUrl($tenant);
        }

        $customerId = $this->createOrFetchCustomer($tenant, $adminUser);

        try {
            $response = Http::withHeaders(['access_token' => $this->apiKey])
                ->post("{$this->baseUrl}/payments", [
                    'customer' => $customerId,
                    'billingType' => 'UNDEFINED',
                    'value' => $tenant->precoComDesconto(),
                    'dueDate' => now()->addDay()->format('Y-m-d'),
                    'description' => 'PitStop '.$tenant->nomePlano().' — acesso mensal completo',
                    'externalReference' => "tenant:{$tenant->slug}:tier:{$tenant->tier()}",
                ]);

            if ($response->successful()) {
                return $response->json('invoiceUrl');
            }

            Log::warning('[Asaas] Falha ao criar payment', ['status' => $response->status()]);
        } catch (\Throwable $e) {
            Log::error('[Asaas] Erro ao criar payment: '.$e->getMessage());
        }

        // Fallback para link fixo configurado no .env
        return $this->fallbackUrl($tenant);
    }

    /** URL de fallback por tier, com fallback genérico para compatibilidade retroativa */
    private function fallbackUrl(Tenant $tenant): ?string
    {
        $generico = config('services.asaas.payment_link_url');

        return $tenant->isProMax()
            ? (config('services.asaas.payment_link_url_pro_max') ?: $generico)
            : (config('services.asaas.payment_link_url_pro') ?: $generico);
    }
}
