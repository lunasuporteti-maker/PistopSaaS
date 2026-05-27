<?php

namespace Tests\Feature;

use App\Models\Fornecedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\HasTenant;

class FornecedorCrudTest extends TestCase
{
    use RefreshDatabase, HasTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    // ────────────────────────────────────────────────────────────
    // CREATE
    // ────────────────────────────────────────────────────────────

    public function test_admin_pode_criar_fornecedor(): void
    {
        $response = $this->apiAs($this->adminUser)
            ->postJson('/api/fornecedores', [
                'nome'     => 'Auto Peças Silva',
                'telefone' => '(81) 99999-0000',
                'email'    => 'contato@autopecas.com',
            ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nome' => 'Auto Peças Silva']);

        $this->assertDatabaseHas('fornecedores', [
            'nome'      => 'Auto Peças Silva',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_gerente_pode_criar_fornecedor(): void
    {
        $response = $this->apiAs($this->gerenteUser)
            ->postJson('/api/fornecedores', ['nome' => 'Distribuidora XYZ']);

        $response->assertStatus(201);
    }

    public function test_operador_nao_pode_criar_fornecedor(): void
    {
        $this->apiAs($this->operadorUser)
            ->postJson('/api/fornecedores', ['nome' => 'Fornecedor Bloqueado'])
            ->assertStatus(403);
    }

    public function test_nome_e_obrigatorio(): void
    {
        $this->apiAs($this->adminUser)
            ->postJson('/api/fornecedores', ['telefone' => '(81) 3333-0000'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nome']);
    }

    public function test_cnpj_valido_e_aceito(): void
    {
        $this->apiAs($this->adminUser)
            ->postJson('/api/fornecedores', [
                'nome' => 'Empresa Válida LTDA',
                'cnpj' => '11.222.333/0001-81',
            ])
            ->assertStatus(201);
    }

    public function test_cnpj_invalido_e_rejeitado(): void
    {
        $this->apiAs($this->adminUser)
            ->postJson('/api/fornecedores', [
                'nome' => 'Empresa Inválida',
                'cnpj' => '11.111.111/1111-11',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cnpj']);
    }

    // ────────────────────────────────────────────────────────────
    // LIST
    // ────────────────────────────────────────────────────────────

    public function test_lista_fornecedores_paginada(): void
    {
        Fornecedor::factory()->count(3)->create(['tenant_id' => $this->tenant->id]);

        $response = $this->apiAs($this->adminUser)
            ->getJson('/api/fornecedores');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'current_page', 'per_page', 'total']);
    }

    public function test_busca_por_nome(): void
    {
        Fornecedor::factory()->create(['tenant_id' => $this->tenant->id, 'nome' => 'ACME Peças']);
        Fornecedor::factory()->create(['tenant_id' => $this->tenant->id, 'nome' => 'Distribuidora XYZ']);

        $response = $this->apiAs($this->adminUser)
            ->getJson('/api/fornecedores?search=ACME');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('ACME Peças', $data[0]['nome']);
    }

    public function test_isolamento_por_tenant(): void
    {
        // Fornecedor de outro tenant NÃO deve aparecer
        $outroTenant = \App\Models\Tenant::factory()->create();
        Fornecedor::factory()->create(['tenant_id' => $outroTenant->id, 'nome' => 'Fornecedor Outro Tenant']);
        Fornecedor::factory()->create(['tenant_id' => $this->tenant->id, 'nome' => 'Meu Fornecedor']);

        $response = $this->apiAs($this->adminUser)->getJson('/api/fornecedores');
        $nomes = collect($response->json('data'))->pluck('nome');

        $this->assertTrue($nomes->contains('Meu Fornecedor'));
        $this->assertFalse($nomes->contains('Fornecedor Outro Tenant'));
    }

    // ────────────────────────────────────────────────────────────
    // UPDATE
    // ────────────────────────────────────────────────────────────

    public function test_admin_pode_editar_fornecedor(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->apiAs($this->adminUser)
            ->putJson("/api/fornecedores/{$fornecedor->id}", ['nome' => 'Nome Atualizado'])
            ->assertStatus(200)
            ->assertJsonFragment(['nome' => 'Nome Atualizado']);
    }

    // ────────────────────────────────────────────────────────────
    // ARCHIVE / DELETE
    // ────────────────────────────────────────────────────────────

    public function test_destroy_arquiva_fornecedor_por_padrao(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id, 'ativo' => true]);

        $this->apiAs($this->adminUser)
            ->deleteJson("/api/fornecedores/{$fornecedor->id}")
            ->assertStatus(200);

        // SQLite armazena boolean como 0/1
        $this->assertDatabaseHas('fornecedores', [
            'id'   => $fornecedor->id,
            'ativo' => 0,
        ]);
    }

    public function test_operador_nao_pode_arquivar(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->apiAs($this->operadorUser)
            ->deleteJson("/api/fornecedores/{$fornecedor->id}")
            ->assertStatus(403);
    }
}
