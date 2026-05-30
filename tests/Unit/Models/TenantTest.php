<?php

namespace Tests\Unit\Models;

use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Story 4.10 — Grace period de 6 dias antes do bloqueio.
 */
class TenantTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-06-10 14:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    private function tenant(?string $vence, bool $ativo = true): Tenant
    {
        return Tenant::factory()->make([
            'plano_ativo' => $ativo,
            'plano_vence_em' => $vence,
        ]);
    }

    public function test_dias_de_atraso_zero_quando_vencimento_futuro(): void
    {
        $this->assertSame(0, $this->tenant('2026-06-15')->diasDeAtraso());
    }

    public function test_dias_de_atraso_zero_quando_vence_hoje(): void
    {
        $this->assertSame(0, $this->tenant('2026-06-10')->diasDeAtraso());
    }

    public function test_dias_de_atraso_zero_quando_nulo(): void
    {
        $this->assertSame(0, $this->tenant(null)->diasDeAtraso());
    }

    public function test_dias_de_atraso_um_quando_venceu_ontem(): void
    {
        $this->assertSame(1, $this->tenant('2026-06-09')->diasDeAtraso());
    }

    public function test_dias_de_atraso_seis_no_limite_do_grace(): void
    {
        $this->assertSame(6, $this->tenant('2026-06-04')->diasDeAtraso());
    }

    public function test_em_grace_period_true_no_dia_3(): void
    {
        $this->assertTrue($this->tenant('2026-06-07')->emGracePeriod());
    }

    public function test_em_grace_period_true_no_dia_6(): void
    {
        $this->assertTrue($this->tenant('2026-06-04')->emGracePeriod());
    }

    public function test_em_grace_period_false_no_dia_7(): void
    {
        $this->assertFalse($this->tenant('2026-06-03')->emGracePeriod());
    }

    public function test_em_grace_period_false_quando_em_dia(): void
    {
        $this->assertFalse($this->tenant('2026-06-15')->emGracePeriod());
    }

    public function test_em_grace_period_false_quando_plano_inativo(): void
    {
        $this->assertFalse($this->tenant('2026-06-07', ativo: false)->emGracePeriod());
    }

    public function test_em_dia_continua_false_durante_grace_period(): void
    {
        // Opção B: emDia() permanece semântico (vencido = false) mesmo no grace period
        $this->assertFalse($this->tenant('2026-06-07')->emDia());
    }
}
