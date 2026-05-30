<?php

namespace Tests\Unit\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Services\AsaasService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Story 4.9 — Correção e auditoria do valor Pro Max no Asaas.
 */
class AsaasServiceTest extends TestCase
{
    private function tenant(string $tier, int $desconto = 0): Tenant
    {
        return Tenant::factory()->make([
            'plano_tier' => $tier,
            'plano_ativo' => true,
            'desconto_percentual' => $desconto,
        ]);
    }

    private function adminUser(): User
    {
        return User::factory()->make([
            'name' => 'Dono Oficina',
            'email' => 'dono@oficina.test',
        ]);
    }

    private function fakeAsaasOk(): void
    {
        Http::fake([
            '*/customers' => Http::response(['id' => 'cus_123'], 200),
            '*/payments' => Http::response(['invoiceUrl' => 'https://sandbox.asaas.com/i/abc'], 200),
        ]);
    }

    public function test_pro_sem_desconto_envia_value_99_90(): void
    {
        config(['services.asaas.api_key' => 'fake-key']);
        $this->fakeAsaasOk();

        (new AsaasService)->createCheckoutUrl($this->tenant('pro'), $this->adminUser());

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/payments') && $request['value'] === 99.90;
        });
    }

    public function test_pro_max_sem_desconto_envia_value_157_50(): void
    {
        config(['services.asaas.api_key' => 'fake-key']);
        $this->fakeAsaasOk();

        (new AsaasService)->createCheckoutUrl($this->tenant('pro_max'), $this->adminUser());

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/payments') && $request['value'] === 157.50;
        });
    }

    public function test_pro_max_com_20_porcento_envia_value_126_00(): void
    {
        config(['services.asaas.api_key' => 'fake-key']);
        $this->fakeAsaasOk();

        (new AsaasService)->createCheckoutUrl($this->tenant('pro_max', 20), $this->adminUser());

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/payments') && $request['value'] === 126.00;
        });
    }

    public function test_fallback_retorna_url_tier_pro_max_quando_nao_configurado(): void
    {
        config([
            'services.asaas.api_key' => '',
            'services.asaas.payment_link_url' => 'https://generic.test',
            'services.asaas.payment_link_url_pro' => 'https://pro.test',
            'services.asaas.payment_link_url_pro_max' => 'https://promax.test',
        ]);

        $url = (new AsaasService)->createCheckoutUrl($this->tenant('pro_max'), $this->adminUser());

        $this->assertSame('https://promax.test', $url);
    }

    public function test_fallback_retorna_url_tier_pro_quando_api_falha(): void
    {
        config([
            'services.asaas.api_key' => 'fake-key',
            'services.asaas.payment_link_url' => 'https://generic.test',
            'services.asaas.payment_link_url_pro' => 'https://pro.test',
            'services.asaas.payment_link_url_pro_max' => 'https://promax.test',
        ]);

        Http::fake([
            '*/customers' => Http::response(['id' => 'cus_123'], 200),
            '*/payments' => Http::response(['error' => 'fail'], 500),
        ]);

        $url = (new AsaasService)->createCheckoutUrl($this->tenant('pro'), $this->adminUser());

        $this->assertSame('https://pro.test', $url);
    }

    public function test_fallback_usa_generico_quando_tier_especifico_vazio(): void
    {
        config([
            'services.asaas.api_key' => '',
            'services.asaas.payment_link_url' => 'https://generic.test',
            'services.asaas.payment_link_url_pro' => '',
            'services.asaas.payment_link_url_pro_max' => '',
        ]);

        $url = (new AsaasService)->createCheckoutUrl($this->tenant('pro_max'), $this->adminUser());

        $this->assertSame('https://generic.test', $url);
    }
}
