<?php

namespace Tests\Traits;

use App\Models\Tenant;
use App\Models\User;

/**
 * Trait para testes de API que requerem contexto de tenant.
 * Cria um tenant, vincula ao IoC container e cria usuário autenticado.
 */
trait HasTenant
{
    protected Tenant $tenant;
    protected User $adminUser;
    protected User $gerenteUser;
    protected User $operadorUser;

    protected function setUpTenant(): void
    {
        $this->tenant = Tenant::factory()->create();

        // Bind tenant no IoC (simula o IdentifyTenant middleware)
        app()->instance('tenant', $this->tenant);
        app()->instance(Tenant::class, $this->tenant);

        // Desabilitar o middleware IdentifyTenant (que re-resolve por host)
        // e ResolveTenantMiddleware equivalente para os testes
        $this->withoutMiddleware(\App\Http\Middleware\IdentifyTenant::class);

        $this->adminUser   = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'perfil'    => 'admin',
        ]);

        $this->gerenteUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'perfil'    => 'gerente',
        ]);

        $this->operadorUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'perfil'    => 'operador',
        ]);
    }

    /**
     * Retorna headers de autenticação Sanctum para uso em actingAs.
     */
    protected function apiAs(User $user): static
    {
        return $this->actingAs($user, 'sanctum');
    }
}
