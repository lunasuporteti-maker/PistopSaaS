<?php

namespace Tests\Feature;

use App\Models\EntradaEstoque;
use App\Models\Fornecedor;
use App\Models\HistoricoEstoque;
use App\Models\Peca;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\HasTenant;

class EntradaEstoqueCriarTest extends TestCase
{
    use RefreshDatabase, HasTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    // ────────────────────────────────────────────────────────────
    // Fluxo completo
    // ────────────────────────────────────────────────────────────

    public function test_admin_pode_criar_entrada_com_tres_itens(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        $pecas = Peca::factory()->count(3)->create([
            'tenant_id'  => $this->tenant->id,
            'quantidade' => 5,
        ]);

        $payload = [
            'fornecedor_id'  => $fornecedor->id,
            'data_entrada'   => now()->toDateString(),
            'tipo_documento' => 'nota_manual',
            'itens'          => [
                ['peca_id' => $pecas[0]->id, 'quantidade' => 10, 'preco_custo_unitario' => 12.50],
                ['peca_id' => $pecas[1]->id, 'quantidade' => 5,  'preco_custo_unitario' => 8.00],
                ['peca_id' => $pecas[2]->id, 'quantidade' => 3,  'preco_custo_unitario' => 25.00],
            ],
        ];

        $response = $this->apiAs($this->adminUser)
            ->postJson('/api/entradas-estoque', $payload);

        $response->assertStatus(201);

        $body = $response->json();

        // Número de entrada gerado corretamente
        $this->assertStringStartsWith('ENT-' . now()->year . '-', $body['numero_entrada']);

        // 3 itens na resposta
        $this->assertCount(3, $body['itens']);

        // valor_total calculado: (10×12.50) + (5×8.00) + (3×25.00) = 125 + 40 + 75 = 240
        $this->assertEquals('240.00', $body['valor_total']);

        // Estoque incrementado
        $this->assertDatabaseHas('pecas', ['id' => $pecas[0]->id, 'quantidade' => 15]);
        $this->assertDatabaseHas('pecas', ['id' => $pecas[1]->id, 'quantidade' => 10]);
        $this->assertDatabaseHas('pecas', ['id' => $pecas[2]->id, 'quantidade' => 8]);

        // Preço de custo atualizado (último custo)
        $this->assertDatabaseHas('pecas', ['id' => $pecas[0]->id, 'preco_custo' => 12.50]);

        // 3 registros no historico_estoque
        $this->assertDatabaseCount('historico_estoque', 3);

        $this->assertDatabaseHas('historico_estoque', [
            'peca_id'          => $pecas[0]->id,
            'tipo'             => 'entrada',
            'quantidade_antes' => 5,
            'quantidade_depois' => 15,
            'quantidade_delta' => 10,
        ]);
    }

    public function test_numero_entrada_e_sequencial_por_tenant(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);
        $peca       = Peca::factory()->create(['tenant_id' => $this->tenant->id, 'quantidade' => 10]);

        $itemBase = [['peca_id' => $peca->id, 'quantidade' => 1, 'preco_custo_unitario' => 5.00]];

        $this->apiAs($this->adminUser)
            ->postJson('/api/entradas-estoque', [
                'fornecedor_id' => $fornecedor->id,
                'itens'         => $itemBase,
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['numero_entrada' => 'ENT-' . now()->year . '-0001']);

        $this->apiAs($this->adminUser)
            ->postJson('/api/entradas-estoque', [
                'fornecedor_id' => $fornecedor->id,
                'itens'         => $itemBase,
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['numero_entrada' => 'ENT-' . now()->year . '-0002']);
    }

    // ────────────────────────────────────────────────────────────
    // Rollback
    // ────────────────────────────────────────────────────────────

    public function test_peca_id_invalido_retorna_422_sem_alterar_estoque(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);
        $peca       = Peca::factory()->create(['tenant_id' => $this->tenant->id, 'quantidade' => 10]);

        $estoqueAntes = $peca->quantidade;

        $this->apiAs($this->adminUser)
            ->postJson('/api/entradas-estoque', [
                'fornecedor_id' => $fornecedor->id,
                'itens'         => [
                    ['peca_id' => $peca->id,    'quantidade' => 5, 'preco_custo_unitario' => 10.00],
                    ['peca_id' => 99999,          'quantidade' => 3, 'preco_custo_unitario' => 10.00], // inválido
                ],
            ])
            ->assertStatus(422);

        // Estoque não alterado (rollback)
        $this->assertDatabaseHas('pecas', ['id' => $peca->id, 'quantidade' => $estoqueAntes]);
        $this->assertDatabaseCount('historico_estoque', 0);
        $this->assertDatabaseCount('entradas_estoque', 0);
    }

    // ────────────────────────────────────────────────────────────
    // Upload de anexo
    // ────────────────────────────────────────────────────────────

    public function test_upload_de_pdf_salva_anexo_path(): void
    {
        Storage::fake('local');

        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);
        $peca       = Peca::factory()->create(['tenant_id' => $this->tenant->id, 'quantidade' => 10]);

        $pdf = UploadedFile::fake()->create('nota.pdf', 200, 'application/pdf');

        $response = $this->apiAs($this->adminUser)
            ->postJson('/api/entradas-estoque', [
                'fornecedor_id' => $fornecedor->id,
                'itens'         => [
                    ['peca_id' => $peca->id, 'quantidade' => 2, 'preco_custo_unitario' => 15.00],
                ],
                'anexo'         => $pdf,
            ]);

        $response->assertStatus(201);

        $entradaId = $response->json('id');
        $anexoPath = $response->json('anexo_path');

        $this->assertNotNull($anexoPath);
        $this->assertStringStartsWith("tenants/{$this->tenant->id}/entradas/", $anexoPath);
        Storage::disk('local')->assertExists($anexoPath);
    }

    // ────────────────────────────────────────────────────────────
    // Permissões
    // ────────────────────────────────────────────────────────────

    public function test_operador_nao_pode_criar_entrada(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->apiAs($this->operadorUser)
            ->postJson('/api/entradas-estoque', [
                'fornecedor_id' => $fornecedor->id,
                'itens'         => [
                    ['peca_id' => 1, 'quantidade' => 1, 'preco_custo_unitario' => 5.00],
                ],
            ])
            ->assertStatus(403);
    }

    public function test_itens_obrigatorio_e_minimo_um(): void
    {
        $fornecedor = Fornecedor::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->apiAs($this->adminUser)
            ->postJson('/api/entradas-estoque', [
                'fornecedor_id' => $fornecedor->id,
                'itens'         => [],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['itens']);
    }

    public function test_fornecedor_id_obrigatorio(): void
    {
        $this->apiAs($this->adminUser)
            ->postJson('/api/entradas-estoque', [
                'itens' => [
                    ['peca_id' => 1, 'quantidade' => 1, 'preco_custo_unitario' => 5.00],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['fornecedor_id']);
    }
}
