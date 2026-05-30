<?php

namespace Tests\Feature\Admin;

use App\Models\OrdemServico;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Story 8.3 — Dashboard admin: KPIs, top-10 e coluna OS/mês.
 */
class AdminMetricasTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::factory()->create([
            'perfil'    => 'super_admin',
            'tenant_id' => null,
        ]);
    }

    public function test_dashboard_exibe_kpi_plano_pago(): void
    {
        Tenant::factory()->create(['plano_ativo' => true, 'plano_vence_em' => now()->addMonth()]);

        Cache::forget('admin.metricas');

        $this->actingAs($this->superAdmin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Plano Pago');
    }

    public function test_dashboard_exibe_tabela_mais_ativos(): void
    {
        $tenant = Tenant::factory()->create(['plano_ativo' => true]);

        // Cria cliente mínimo no tenant
        $cliente = \App\Models\Cliente::withoutGlobalScope('tenant')->forceCreate([
            'tenant_id' => $tenant->id,
            'nome'      => 'Cliente Teste',
        ]);

        $veiculo = \App\Models\Veiculo::withoutGlobalScope('tenant')->forceCreate([
            'tenant_id'  => $tenant->id,
            'cliente_id' => $cliente->id,
            'marca'      => 'Fiat',
            'modelo'     => 'Uno',
            'ano'        => 2020,
            'placa'      => 'TST0001',
        ]);

        // Cria 3 OS no último mês para este tenant
        for ($i = 0; $i < 3; $i++) {
            OrdemServico::withoutGlobalScope('tenant')->forceCreate([
                'tenant_id'   => $tenant->id,
                'numero_os'   => 'OS-TEST-' . $i . '-' . $tenant->id,
                'created_at'  => now()->subDays(5),
                'updated_at'  => now()->subDays(5),
                'status'      => 'aberta',
                'valor_total' => 0,
                'cliente_id'  => $cliente->id,
                'veiculo_id'  => $veiculo->id,
            ]);
        }

        Cache::forget('admin.metricas');

        $this->actingAs($this->superAdmin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Mais ativos')
            ->assertSee($tenant->nome);
    }

    public function test_indice_tenants_exibe_coluna_os_mes(): void
    {
        $tenant = Tenant::factory()->create();

        $this->actingAs($this->superAdmin)
            ->get('/admin/tenants')
            ->assertOk()
            ->assertSee('OS/mês');
    }
}
