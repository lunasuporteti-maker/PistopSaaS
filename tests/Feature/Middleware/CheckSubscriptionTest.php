<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\CheckSubscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Story 4.10 — Grace period de 6 dias no CheckSubscription.
 */
class CheckSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private CheckSubscription $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-06-10 14:00:00'));
        $this->middleware = new CheckSubscription;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    private function bootTenant(array $attrs): User
    {
        $tenant = Tenant::factory()->create($attrs);
        app()->instance('tenant', $tenant);
        app()->instance(Tenant::class, $tenant);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'perfil' => 'admin',
        ]);
        $this->actingAs($user);

        return $user;
    }

    private function request(string $method, bool $json = false): Request
    {
        $request = Request::create('/dashboard', $method);
        if ($json) {
            $request->headers->set('Accept', 'application/json');
        }

        return $request;
    }

    private function passNext(): \Closure
    {
        return fn ($req) => response('ok', 200);
    }

    public function test_grace_period_dia_3_libera_get_e_post_com_flash(): void
    {
        $this->bootTenant([
            'plano_ativo' => true,
            'plano_vence_em' => '2026-06-07', // venceu há 3 dias
        ]);

        $get = $this->middleware->handle($this->request('GET'), $this->passNext());
        $this->assertSame(200, $get->getStatusCode());

        $post = $this->middleware->handle($this->request('POST'), $this->passNext());
        $this->assertSame(200, $post->getStatusCode());

        $this->assertEquals(3, session('grace_period_dias'));
    }

    public function test_grace_period_esgotado_dia_7_bloqueia_post_json(): void
    {
        $this->bootTenant([
            'plano_ativo' => true,
            'plano_vence_em' => '2026-06-03', // venceu há 7 dias
        ]);

        $response = $this->middleware->handle($this->request('POST', json: true), $this->passNext());

        $this->assertSame(402, $response->getStatusCode());
        $this->assertSame(
            'Plano vencido. Regularize seu pagamento para continuar.',
            $response->getData(true)['message']
        );
    }

    public function test_grace_period_esgotado_dia_7_libera_get_json(): void
    {
        $this->bootTenant([
            'plano_ativo' => true,
            'plano_vence_em' => '2026-06-03',
        ]);

        $response = $this->middleware->handle($this->request('GET', json: true), $this->passNext());

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_plano_em_dia_nao_seta_flash_de_grace(): void
    {
        $this->bootTenant([
            'plano_ativo' => true,
            'plano_vence_em' => '2026-06-20', // futuro
        ]);

        $response = $this->middleware->handle($this->request('POST'), $this->passNext());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNull(session('grace_period_dias'));
    }

    public function test_trial_ativo_nao_e_afetado_por_grace_period(): void
    {
        $tenant = Tenant::factory()->create([
            'plano_ativo' => false,
            'plano_vence_em' => null,
            'trial_ends_at' => Carbon::parse('2026-06-20'), // trial ativo
        ]);
        app()->instance('tenant', $tenant);
        app()->instance(Tenant::class, $tenant);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'perfil' => 'admin']);
        $this->actingAs($user);

        $response = $this->middleware->handle($this->request('POST'), $this->passNext());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNull(session('grace_period_dias'));
    }

    public function test_tenant_legado_nao_e_afetado(): void
    {
        $tenant = Tenant::factory()->create([
            'plano_ativo' => false,
            'plano_vence_em' => null,
            'trial_ends_at' => null, // legado
        ]);
        app()->instance('tenant', $tenant);
        app()->instance(Tenant::class, $tenant);
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'perfil' => 'admin']);
        $this->actingAs($user);

        $response = $this->middleware->handle($this->request('POST'), $this->passNext());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNull(session('grace_period_dias'));
    }
}
