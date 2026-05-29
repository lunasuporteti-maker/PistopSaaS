<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Orcamento;
use App\Models\OrcamentoInteracao;
use App\Models\ServicoFoto;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Tests\Traits\HasTenant;

/**
 * Story 2.1 — fundação de banco do Portal v2.
 * Valida schema, imutabilidade de orcamento_interacoes, soft delete de servico_fotos
 * e os novos campos de aprovação em orcamentos.
 */
class PortalV2SchemaTest extends TestCase
{
    use RefreshDatabase, HasTenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    private function criarOrcamento(): Orcamento
    {
        $cliente = Cliente::create([
            'tenant_id' => $this->tenant->id,
            'nome'      => 'Cliente Teste',
            'telefone'  => '84999999999',
        ]);

        $veiculo = Veiculo::create([
            'tenant_id'  => $this->tenant->id,
            'cliente_id' => $cliente->id,
            'marca'      => 'Honda',
            'modelo'     => 'CG 160',
        ]);

        return Orcamento::create([
            'tenant_id'   => $this->tenant->id,
            'cliente_id'  => $cliente->id,
            'veiculo_id'  => $veiculo->id,
            'status'      => 'orcamento',
            'valor_total' => 250.00,
        ]);
    }

    public function test_tabelas_e_colunas_foram_criadas(): void
    {
        $this->assertTrue(Schema::hasTable('orcamento_interacoes'));
        $this->assertTrue(Schema::hasTable('servico_fotos'));

        $this->assertTrue(Schema::hasColumns('orcamentos', [
            'aprovado_por_canal', 'aprovado_ip', 'aprovado_user_agent',
        ]));

        // orcamento_interacoes é insert-only: NÃO deve ter updated_at
        $this->assertFalse(Schema::hasColumn('orcamento_interacoes', 'updated_at'));
        $this->assertTrue(Schema::hasColumn('orcamento_interacoes', 'created_at'));
    }

    public function test_orcamento_aceita_campos_de_aprovacao(): void
    {
        $orcamento = $this->criarOrcamento();

        $orcamento->update([
            'status'              => 'aprovado',
            'aprovado_em'         => now(),
            'aprovado_por_canal'  => Orcamento::CANAL_PORTAL,
            'aprovado_ip'         => '203.0.113.42',
            'aprovado_user_agent' => 'Mozilla/5.0 Test',
        ]);

        $this->assertDatabaseHas('orcamentos', [
            'id'                 => $orcamento->id,
            'status'             => 'aprovado',
            'aprovado_por_canal' => 'portal',
            'aprovado_ip'        => '203.0.113.42',
        ]);
    }

    public function test_orcamento_interacao_e_insert_only_e_casta_json(): void
    {
        $orcamento = $this->criarOrcamento();

        $interacao = OrcamentoInteracao::create([
            'tenant_id'    => $this->tenant->id,
            'orcamento_id' => $orcamento->id,
            'tipo'         => OrcamentoInteracao::TIPO_APROVACAO,
            'dados_json'   => ['ip' => '203.0.113.42', 'aceite_termos' => true],
            'usuario_id'   => null,
        ]);

        // dados_json é castado para array
        $this->assertIsArray($interacao->fresh()->dados_json);
        $this->assertTrue($interacao->fresh()->dados_json['aceite_termos']);

        // Insert-only: model não tenta escrever updated_at
        $this->assertNull(OrcamentoInteracao::UPDATED_AT);

        $this->assertDatabaseHas('orcamento_interacoes', [
            'id'   => $interacao->id,
            'tipo' => 'aprovacao',
        ]);
    }

    public function test_servico_foto_suporta_soft_delete(): void
    {
        $orcamento = $this->criarOrcamento();

        $foto = ServicoFoto::create([
            'tenant_id'     => $this->tenant->id,
            'orcamento_id'  => $orcamento->id,
            'categoria'     => ServicoFoto::CATEGORIA_ANTES,
            'path_original' => 'tenants/1/fotos/foto.jpg',
            'tamanho_bytes' => 12345,
            'mime_type'     => 'image/jpeg',
            'uploaded_by'   => $this->adminUser->id,
        ]);

        $foto->delete();

        $this->assertSoftDeleted('servico_fotos', ['id' => $foto->id]);
        $this->assertEquals(0, ServicoFoto::where('id', $foto->id)->count());
        $this->assertEquals(1, ServicoFoto::withTrashed()->where('id', $foto->id)->count());
    }
}
