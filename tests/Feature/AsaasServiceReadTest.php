<?php

namespace Tests\Feature;

use App\Services\AsaasService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Story 6.1 — Métodos de leitura do AsaasService (listarPagamentos / pagamentosPendentes).
 * Inclui semântica nullable antecipada da Story 6.4 (null = falha, [] = sem dados).
 */
class AsaasServiceReadTest extends TestCase
{
    private const CUSTOMER = 'cus_000123456';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.asaas.api_key' => 'test-key', 'services.asaas.environment' => 'sandbox']);
        Cache::flush();
    }

    private function service(): AsaasService
    {
        return new AsaasService;
    }

    private function paymentsUrl(): string
    {
        return 'sandbox.asaas.com/api/v3/payments*';
    }

    public function test_listar_pagamentos_retorna_array_de_data(): void
    {
        Http::fake([
            $this->paymentsUrl() => Http::response([
                'data' => [
                    ['id' => 'pay_1', 'value' => 99.90, 'status' => 'RECEIVED'],
                    ['id' => 'pay_2', 'value' => 99.90, 'status' => 'PENDING'],
                ],
            ], 200),
        ]);

        $result = $this->service()->listarPagamentos(self::CUSTOMER);

        $this->assertCount(2, $result);
        $this->assertSame('pay_1', $result[0]['id']);
    }

    public function test_pagamentos_pendentes_merge_pending_e_overdue(): void
    {
        Http::fake([
            'sandbox.asaas.com/api/v3/payments?customer='.self::CUSTOMER.'&status=PENDING' => Http::response([
                'data' => [['id' => 'pay_pending', 'status' => 'PENDING']],
            ], 200),
            'sandbox.asaas.com/api/v3/payments?customer='.self::CUSTOMER.'&status=OVERDUE' => Http::response([
                'data' => [['id' => 'pay_overdue', 'status' => 'OVERDUE']],
            ], 200),
        ]);

        $result = $this->service()->pagamentosPendentes(self::CUSTOMER);

        $this->assertCount(2, $result);
        $ids = array_column($result, 'id');
        $this->assertContains('pay_pending', $ids);
        $this->assertContains('pay_overdue', $ids);
    }

    public function test_timeout_retorna_null(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('cURL error 28: timeout');
        });

        $this->assertNull($this->service()->listarPagamentos(self::CUSTOMER));
    }

    public function test_erro_5xx_retorna_null(): void
    {
        Http::fake([$this->paymentsUrl() => Http::response(null, 500)]);

        $this->assertNull($this->service()->listarPagamentos(self::CUSTOMER));
    }

    public function test_erro_404_retorna_array_vazio(): void
    {
        Http::fake([$this->paymentsUrl() => Http::response(null, 404)]);

        $this->assertSame([], $this->service()->listarPagamentos(self::CUSTOMER));
    }

    public function test_resposta_sem_dados_retorna_array_vazio(): void
    {
        Http::fake([$this->paymentsUrl() => Http::response(['data' => []], 200)]);

        $this->assertSame([], $this->service()->listarPagamentos(self::CUSTOMER));
    }

    public function test_pagamentos_pendentes_propaga_null_quando_uma_chamada_falha(): void
    {
        Http::fake([
            'sandbox.asaas.com/api/v3/payments?customer='.self::CUSTOMER.'&status=PENDING' => Http::response(['data' => []], 200),
            'sandbox.asaas.com/api/v3/payments?customer='.self::CUSTOMER.'&status=OVERDUE' => Http::response(null, 500),
        ]);

        $this->assertNull($this->service()->pagamentosPendentes(self::CUSTOMER));
    }

    public function test_cache_hit_nao_refaz_chamada_http(): void
    {
        Http::fake([$this->paymentsUrl() => Http::response(['data' => [['id' => 'pay_1']]], 200)]);

        $service = $this->service();
        $service->listarPagamentos(self::CUSTOMER);
        $service->listarPagamentos(self::CUSTOMER);

        Http::assertSentCount(1);
    }

    public function test_nao_configurado_retorna_array_vazio_sem_http(): void
    {
        config(['services.asaas.api_key' => '']);
        Http::fake();

        $this->assertSame([], $this->service()->listarPagamentos(self::CUSTOMER));
        $this->assertSame([], $this->service()->pagamentosPendentes(self::CUSTOMER));
        Http::assertNothingSent();
    }

    public function test_customer_id_nunca_aparece_em_log_raw(): void
    {
        Http::fake([$this->paymentsUrl() => Http::response(['data' => []], 200)]);

        $mensagens = [];
        Log::listen(function ($log) use (&$mensagens) {
            $mensagens[] = $log->message.' '.json_encode($log->context);
        });

        $this->service()->listarPagamentos(self::CUSTOMER);

        $joined = implode("\n", $mensagens);
        $this->assertStringNotContainsString(self::CUSTOMER, $joined);
        $this->assertStringContainsString(hash('sha256', self::CUSTOMER), $joined);
    }
}
