<?php

namespace Tests\Unit\Commands;

use App\Mail\PlanoExpirando1DiaMail;
use App\Mail\PlanoExpirando3DiasMail;
use App\Mail\PlanoExpirando7DiasMail;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Story 8.1 — CheckSubscriptionExpiryCommand: envia emails D-7, D-3, D-1 sem duplicata.
 */
class CheckSubscriptionExpiryCommandTest extends TestCase
{
    use RefreshDatabase;

    private function tenantComPlano(string $venceEm): Tenant
    {
        $tenant = Tenant::factory()->create([
            'plano_ativo'   => true,
            'plano_vence_em' => $venceEm,
        ]);

        \App\Models\User::factory()->create([
            'tenant_id' => $tenant->id,
            'perfil'    => 'admin',
            'email'     => "admin-{$tenant->id}@test.com",
        ]);

        return $tenant;
    }

    public function test_envia_email_d7(): void
    {
        Mail::fake();
        $this->tenantComPlano(now()->addDays(7)->toDateString());

        $this->artisan('pitstop:check-subscription-expiry')->assertSuccessful();

        Mail::assertQueued(PlanoExpirando7DiasMail::class);
    }

    public function test_envia_email_d3(): void
    {
        Mail::fake();
        $this->tenantComPlano(now()->addDays(3)->toDateString());

        $this->artisan('pitstop:check-subscription-expiry')->assertSuccessful();

        Mail::assertQueued(PlanoExpirando3DiasMail::class);
    }

    public function test_envia_email_d1(): void
    {
        Mail::fake();
        $this->tenantComPlano(now()->addDays(1)->toDateString());

        $this->artisan('pitstop:check-subscription-expiry')->assertSuccessful();

        Mail::assertQueued(PlanoExpirando1DiaMail::class);
    }

    public function test_nao_envia_quando_nao_e_o_dia_certo(): void
    {
        Mail::fake();
        $this->tenantComPlano(now()->addDays(5)->toDateString()); // D-5

        $this->artisan('pitstop:check-subscription-expiry')->assertSuccessful();

        Mail::assertNothingQueued();
    }

    public function test_nao_duplica_email_ja_enviado(): void
    {
        Mail::fake();
        $tenant = $this->tenantComPlano(now()->addDays(7)->toDateString());

        // Registra que D-7 já foi enviado
        \App\Models\SubscriptionLog::create([
            'tenant_id'    => $tenant->id,
            'evento'       => 'email_plano_expirando_7_dias',
            'payload_json' => '{}',
        ]);

        $this->artisan('pitstop:check-subscription-expiry')->assertSuccessful();

        Mail::assertNothingQueued();
    }

    public function test_ignora_tenant_sem_plano_pago(): void
    {
        Mail::fake();
        Tenant::factory()->create([
            'plano_ativo'    => false,
            'plano_vence_em' => now()->addDays(7)->toDateString(),
        ]);

        $this->artisan('pitstop:check-subscription-expiry')->assertSuccessful();

        Mail::assertNothingQueued();
    }
}
