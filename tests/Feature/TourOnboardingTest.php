<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\HasTenant;

/**
 * Story 8.2 — Tour de onboarding: exibição condicional, concluir e resetar.
 */
class TourOnboardingTest extends TestCase
{
    use HasTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_tour_aparece_para_admin_novo_sem_completar(): void
    {
        $this->tenant->forceFill(['created_at' => now()])->save();
        $this->adminUser->update(['onboarding_tour_completo' => false]);

        $this->actingAs($this->adminUser)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('onboarding-tour.js');
    }

    public function test_tour_nao_aparece_para_admin_que_completou(): void
    {
        $this->tenant->forceFill(['created_at' => now()])->save();
        $this->adminUser->update(['onboarding_tour_completo' => true]);

        $this->actingAs($this->adminUser)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('onboarding-tour.js');
    }

    public function test_tour_aparece_independente_da_idade_do_tenant(): void
    {
        // O tour é por usuário (onboarding_tour_completo), não depende da
        // idade do tenant — todo usuário vê no primeiro acesso.
        $this->tenant->forceFill(['created_at' => now()->subDays(10)])->save();
        $this->adminUser->update(['onboarding_tour_completo' => false]);

        $this->actingAs($this->adminUser)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('onboarding-tour.js');
    }

    public function test_tour_aparece_para_gerente_no_primeiro_acesso(): void
    {
        // Tour exibido para todos os perfis (não só admin) no primeiro login.
        $this->tenant->forceFill(['created_at' => now()])->save();
        $this->gerenteUser->update(['onboarding_tour_completo' => false]);

        $this->actingAs($this->gerenteUser)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('onboarding-tour.js');
    }

    public function test_concluir_tour_salva_flag(): void
    {
        $this->actingAs($this->adminUser)
            ->postJson('/tour/concluir')
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('users', [
            'id'                       => $this->adminUser->id,
            'onboarding_tour_completo' => true,
        ]);
    }

    public function test_resetar_tour_limpa_flag_e_redireciona(): void
    {
        $this->adminUser->update(['onboarding_tour_completo' => true]);

        $this->actingAs($this->adminUser)
            ->post('/tour/resetar')
            ->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'id'                       => $this->adminUser->id,
            'onboarding_tour_completo' => false,
        ]);
    }
}
