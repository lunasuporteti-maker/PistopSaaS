<?php

namespace Tests\Feature\Admin;

use App\Models\Tenant;
use App\Models\User;
use App\Models\UserLoginLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Story 7.1 — Super admin vê histórico dos últimos 3 logins por usuário.
 */
class AdminLoginHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'perfil'    => 'super_admin',
            'tenant_id' => null,
        ]);

        $this->tenant = Tenant::factory()->create();
    }

    private function url(): string
    {
        return '/admin/tenants/' . $this->tenant->id;
    }

    public function test_coluna_ultimos_logins_aparece_na_tabela(): void
    {
        User::factory()->create(['tenant_id' => $this->tenant->id, 'perfil' => 'admin']);

        $this->actingAs($this->superAdmin)
            ->get($this->url())
            ->assertOk()
            ->assertSee('Últimos Logins');
    }

    public function test_usuario_sem_logins_exibe_traco(): void
    {
        User::factory()->create(['tenant_id' => $this->tenant->id, 'perfil' => 'admin']);

        $this->actingAs($this->superAdmin)
            ->get($this->url())
            ->assertOk()
            ->assertSee('—');
    }

    public function test_exibe_data_do_ultimo_login_formatada(): void
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id, 'perfil' => 'admin']);

        UserLoginLog::create([
            'user_id'      => $user->id,
            'logged_in_at' => '2026-06-01 14:30:00',
        ]);

        $this->actingAs($this->superAdmin)
            ->get($this->url())
            ->assertOk()
            ->assertSee('01/06/2026 14:30');
    }

    public function test_exibe_no_maximo_3_logins_por_usuario(): void
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id, 'perfil' => 'admin']);

        UserLoginLog::create(['user_id' => $user->id, 'logged_in_at' => '2026-05-28 08:00:00']);
        UserLoginLog::create(['user_id' => $user->id, 'logged_in_at' => '2026-05-29 09:00:00']);
        UserLoginLog::create(['user_id' => $user->id, 'logged_in_at' => '2026-05-30 10:00:00']);

        $response = $this->actingAs($this->superAdmin)->get($this->url());

        $response->assertOk()
            ->assertSee('30/05/2026 10:00')
            ->assertSee('29/05/2026 09:00')
            ->assertSee('28/05/2026 08:00');
    }

    public function test_nao_exibe_logins_de_outros_tenants(): void
    {
        $outraTenant = Tenant::factory()->create();
        $outroUser   = User::factory()->create(['tenant_id' => $outraTenant->id, 'perfil' => 'admin']);

        UserLoginLog::create([
            'user_id'      => $outroUser->id,
            'logged_in_at' => '2026-06-01 12:00:00',
        ]);

        // Nenhum usuário no tenant atual
        $response = $this->actingAs($this->superAdmin)->get($this->url());
        $response->assertOk()->assertDontSee('01/06/2026 12:00');
    }

    public function test_nao_super_admin_nao_acessa_rota(): void
    {
        $adminComum = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'perfil'    => 'admin',
        ]);

        $this->actingAs($adminComum)
            ->get($this->url())
            ->assertForbidden();
    }
}
