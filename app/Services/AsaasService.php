<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    /** TTL do cache de leitura de cobranças (NFR-050) */
    private const CACHE_TTL_MINUTES = 15;

    /** Timeout máximo das chamadas de leitura à Asaas (NFR-051) */
    private const READ_TIMEOUT_SECONDS = 5;

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
                $this->invalidarCachePagamentos($customerId);

                return $response->json('invoiceUrl');
            }

            Log::warning('[Asaas] Falha ao criar payment', ['status' => $response->status()]);
        } catch (\Throwable $e) {
            Log::error('[Asaas] Erro ao criar payment: '.$e->getMessage());
        }

        // Fallback para link fixo configurado no .env
        return $this->fallbackUrl($tenant);
    }

    /**
     * Lista cobranças de um customer na Asaas (GET /v3/payments).
     *
     * @param  array  $filtros  Filtros opcionais (ex: ['status' => 'PENDING', 'limit' => 12])
     * @return array|null  Array de cobranças; [] sem dados/não configurado/404; null em falha de comunicação (timeout/5xx)
     */
    public function listarPagamentos(string $customerId, array $filtros = []): ?array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $cacheKey = "asaas_payments_{$customerId}_".md5(serialize($filtros));

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($customerId, $filtros) {
            $inicio = microtime(true);

            try {
                $response = Http::withHeaders(['access_token' => $this->apiKey])
                    ->timeout(self::READ_TIMEOUT_SECONDS)
                    ->get("{$this->baseUrl}/payments", array_merge(['customer' => $customerId], $filtros));

                $this->logLeitura('listarPagamentos', '/v3/payments', $customerId, $response->status(), $inicio);

                // Resposta válida da API: 2xx ou 404 (customer sem cobranças) → [] sem dados
                if ($response->successful()) {
                    return $response->json('data') ?? [];
                }

                if ($response->status() === 404) {
                    return [];
                }

                // 4xx (exceto 404) e 5xx → falha → null
                return null;
            } catch (\Throwable $e) {
                $this->logLeitura('listarPagamentos', '/v3/payments', $customerId, 0, $inicio);
                Log::warning('[Asaas] Erro ao listar pagamentos: '.$e->getMessage());

                // Timeout / erro de conexão → falha de comunicação → null
                return null;
            }
        });
    }

    /**
     * Cobranças pendentes (PENDING + OVERDUE) de um customer.
     *
     * @return array|null  Array merged de pendentes/vencidas; [] sem dados/não configurado; null em falha de comunicação
     */
    public function pagamentosPendentes(string $customerId): ?array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $cacheKey = "asaas_pending_{$customerId}";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($customerId) {
            $pending = $this->listarPagamentos($customerId, ['status' => 'PENDING']);
            $overdue = $this->listarPagamentos($customerId, ['status' => 'OVERDUE']);

            // Se qualquer uma das chamadas falhou (null), propaga indisponibilidade
            if ($pending === null || $overdue === null) {
                return null;
            }

            return array_merge($pending, $overdue);
        });
    }

    /** Invalida o cache de cobranças de um customer após nova cobrança criada (NFR-050) */
    private function invalidarCachePagamentos(?string $customerId): void
    {
        if (empty($customerId)) {
            return;
        }

        Cache::forget("asaas_payments_{$customerId}_".md5(serialize([])));
        Cache::forget("asaas_payments_{$customerId}_".md5(serialize(['status' => 'PENDING'])));
        Cache::forget("asaas_payments_{$customerId}_".md5(serialize(['status' => 'OVERDUE'])));
        Cache::forget("asaas_payments_{$customerId}_".md5(serialize(['limit' => 12])));
        Cache::forget("asaas_pending_{$customerId}");
    }

    /** Log de auditoria de leitura — customerId sempre hasheado, nunca em texto (NFR-056) */
    private function logLeitura(string $metodo, string $endpoint, string $customerId, int $statusHttp, float $inicio): void
    {
        Log::info("AsaasService.{$metodo}", [
            'tenant_customer_id_hash' => hash('sha256', $customerId),
            'endpoint' => $endpoint,
            'status_http' => $statusHttp,
            'duracao_ms' => (int) round((microtime(true) - $inicio) * 1000),
        ]);
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
