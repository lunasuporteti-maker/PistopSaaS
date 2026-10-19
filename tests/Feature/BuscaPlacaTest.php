<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Orcamento;
use App\Models\Tenant;
use App\Models\Veiculo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\HasTenant;

/**
 * Story "Busca Interna por Placa" — busca interna (tenant-scoped), os dois ramos,
 * consistência dos pontos de entrada e normalização na gravação.
 * Roda em SQLite (phpunit.xml), sem rede.
 */
class BuscaPlacaTest extends TestCase
{
    use HasTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        // As views estendem layouts.pitstop (usa @vite); sem build de frontend
        // nos testes, stubamos o Vite para renderizar o Blade sem o manifesto.
        $this->withoutVite();
    }

    /**
     * Cria um cliente + veículo. `placaRaw` é gravada SEM normalização
     * (via Model::create) para simular tanto o fluxo limpo quanto o legado sujo.
     */
    private function criarVeiculo(string $placaRaw, array $attrs = [], ?int $tenantId = null): Veiculo
    {
        $tenantId = $tenantId ?? $this->tenant->id;

        $cliente = Cliente::create(array_merge([
            'tenant_id' => $tenantId,
            'nome'      => $attrs['cliente_nome'] ?? 'Cliente Teste',
            'telefone'  => $attrs['cliente_telefone'] ?? '(84) 99999-0000',
        ], []));

        return Veiculo::create([
            'tenant_id'    => $tenantId,
            'cliente_id'   => $cliente->id,
            'placa'        => $placaRaw,
            'marca'        => $attrs['marca'] ?? 'CHEVROLET',
            'modelo'       => $attrs['modelo'] ?? 'ONIX',
            'tipo_veiculo' => $attrs['tipo'] ?? 'carro',
        ]);
    }

    // ── AC2: busca case-insensitive e com hífen/espaço ──────────────────────

    public function test_busca_encontra_independente_de_caixa_hifen_e_espaco(): void
    {
        $veiculo = $this->criarVeiculo('ABC1D23', ['cliente_nome' => 'João da Silva']);

        foreach (['abc1d23', 'ABC1D23', 'ABC-1D23', 'abc 1d23'] as $termo) {
            $this->actingAs($this->adminUser)
                ->get('/veiculos/buscar-placa?placa=' . urlencode($termo))
                ->assertOk()
                ->assertSee('João da Silva')
                ->assertSee('ABC1D23');
        }
    }

    // ── AC3: encontra legado sujo (placa gravada com minúsculas/hífen) ──────

    public function test_busca_encontra_veiculo_legado_sujo(): void
    {
        $this->criarVeiculo('abc-1234', ['cliente_nome' => 'Maria Legado']);

        $this->actingAs($this->adminUser)
            ->get('/veiculos/buscar-placa?placa=ABC1234')
            ->assertOk()
            ->assertSee('Maria Legado');
    }

    // ── AC4: placa vazia/curta orienta e não quebra ─────────────────────────

    public function test_placa_curta_orienta_usuario(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/veiculos/buscar-placa?placa=AB')
            ->assertOk()
            ->assertSee('mínimo 3 caracteres');
    }

    public function test_placa_vazia_orienta_usuario(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/veiculos/buscar-placa?placa=')
            ->assertOk()
            ->assertSee('mínimo 3 caracteres');
    }

    public function test_tela_inicial_sem_parametro_nao_mostra_erro(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/veiculos/buscar-placa')
            ->assertOk()
            ->assertDontSee('mínimo 3 caracteres')
            ->assertDontSee('Nenhum veículo encontrado');
    }

    // ── AC5: ramo encontrado mostra cliente dono + atalhos ──────────────────

    public function test_ramo_encontrado_mostra_cliente_e_atalhos(): void
    {
        $veiculo = $this->criarVeiculo('XYZ2E34', ['cliente_nome' => 'Carlos Dono']);

        $response = $this->actingAs($this->adminUser)
            ->get('/veiculos/buscar-placa?placa=XYZ2E34')
            ->assertOk()
            ->assertSee('Carlos Dono')
            ->assertSee('Ver histórico')
            ->assertSee('Perfil do cliente')
            ->assertSee('Ficha do cliente');

        // Atalhos apontam para os destinos corretos
        $response->assertSee(route('veiculos.show', $veiculo), false);
        $response->assertSee(route('clientes.ficha', $veiculo->cliente), false);
    }

    // ── AC6: ramo não encontrado mostra mensagem + CTAs ─────────────────────

    public function test_ramo_nao_encontrado_mostra_mensagem_e_ctas(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/veiculos/buscar-placa?placa=ZZZ9999')
            ->assertOk()
            ->assertSee('Nenhum veículo encontrado')
            ->assertSee('ZZZ9999')
            ->assertSee('Cadastrar novo veículo')
            ->assertSee('Cadastrar novo cliente');

        // CTA de cadastro leva a placa já normalizada
        $response->assertSee('placa=ZZZ9999', false);
    }

    // ── AC7: orçamentos aparecem no show ────────────────────────────────────

    public function test_show_lista_orcamentos_do_veiculo(): void
    {
        $veiculo = $this->criarVeiculo('OrC1A11');

        Orcamento::create([
            'tenant_id'   => $this->tenant->id,
            'cliente_id'  => $veiculo->cliente_id,
            'veiculo_id'  => $veiculo->id,
            'status'      => 'aprovado',
            'valor_total' => 350.50,
        ]);

        $this->actingAs($this->adminUser)
            ->get(route('veiculos.show', $veiculo))
            ->assertOk()
            ->assertSee('Orçamentos')
            ->assertSee('350,50');
    }

    // ── AC8: isolamento entre tenants ───────────────────────────────────────

    public function test_placa_de_outro_tenant_retorna_nao_encontrada(): void
    {
        $outroTenant = Tenant::factory()->create();
        $this->criarVeiculo('OUT1R00', ['cliente_nome' => 'Dono Outro Tenant'], $outroTenant->id);

        $this->actingAs($this->adminUser)
            ->get('/veiculos/buscar-placa?placa=OUT1R00')
            ->assertOk()
            ->assertSee('Nenhum veículo encontrado')
            ->assertDontSee('Dono Outro Tenant');
    }

    public function test_index_nao_vaza_veiculo_de_outro_tenant_no_or(): void
    {
        $outroTenant = Tenant::factory()->create();
        $this->criarVeiculo('TBZ0B00', ['modelo' => 'FUSCA', 'cliente_nome' => 'Fulano B'], $outroTenant->id);
        $this->criarVeiculo('AAA1A11', ['modelo' => 'GOL', 'cliente_nome' => 'Fulano A']);

        $this->actingAs($this->adminUser)
            ->get('/veiculos?search=FUSCA')
            ->assertOk()
            ->assertDontSee('TBZ0B00')
            ->assertSee('Nenhum veículo encontrado');
    }

    // ── AC9: index usa normalização (case-insensitive, ignora hífen/espaço) ─

    public function test_index_busca_placa_com_hifen(): void
    {
        $this->criarVeiculo('ABC1D23', ['modelo' => 'CRUZE']);

        $this->actingAs($this->adminUser)
            ->get('/veiculos?search=' . urlencode('abc-1d23'))
            ->assertOk()
            ->assertSee('ABC1D23');
    }

    public function test_index_busca_por_modelo_e_cliente_continua_funcionando(): void
    {
        $this->criarVeiculo('MDL1A11', ['modelo' => 'TRACKER', 'cliente_nome' => 'Ana Pereira']);

        $this->actingAs($this->adminUser)
            ->get('/veiculos?search=TRACKER')
            ->assertOk()
            ->assertSee('MDL1A11');

        $this->actingAs($this->adminUser)
            ->get('/veiculos?search=Ana')
            ->assertOk()
            ->assertSee('MDL1A11');
    }

    // ── AC10: scope portável usado pela busca global acha ABC-1234 ──────────

    public function test_scope_parcial_encontra_placa_com_hifen(): void
    {
        $veiculo = $this->criarVeiculo('ABC1234');

        $encontrados = Veiculo::porPlaca('ABC-1234', false)->get();
        $this->assertTrue($encontrados->contains('id', $veiculo->id));

        // Legado sujo também é alcançado pela busca parcial.
        $sujo = $this->criarVeiculo('def-5678');
        $encontradosSujo = Veiculo::porPlaca('DEF5678', false)->get();
        $this->assertTrue($encontradosSujo->contains('id', $sujo->id));
    }

    // ── AC11: normalização na gravação ──────────────────────────────────────

    public function test_store_grava_placa_normalizada(): void
    {
        $cliente = Cliente::create(['tenant_id' => $this->tenant->id, 'nome' => 'Cliente Store']);

        $this->actingAs($this->adminUser)
            ->post('/veiculos', [
                'cliente_id'   => $cliente->id,
                'tipo_veiculo' => 'carro',
                'marca'        => 'FIAT',
                'modelo'       => 'ARGO',
                'placa'        => 'ABC1D23',
                'cor'          => 'PRATA',
                'km_atual'     => '',
            ])
            ->assertRedirect(route('veiculos.index'));

        $this->assertDatabaseHas('veiculos', [
            'cliente_id' => $cliente->id,
            'placa'      => 'ABC1D23',
            'tenant_id'  => $this->tenant->id,
        ]);
    }

    public function test_json_store_veiculo_normaliza_placa_suja(): void
    {
        $cliente = Cliente::create(['tenant_id' => $this->tenant->id, 'nome' => 'Cliente Modal']);

        $this->actingAs($this->adminUser)
            ->postJson('/json/veiculos', [
                'cliente_id' => $cliente->id,
                'marca'      => 'Honda',
                'modelo'     => 'Civic',
                'placa'      => 'abc-1234',
            ])
            ->assertOk();

        $this->assertDatabaseHas('veiculos', [
            'cliente_id' => $cliente->id,
            'placa'      => 'ABC1234',
        ]);
    }

    public function test_json_store_veiculo_sem_placa_mantem_null(): void
    {
        $cliente = Cliente::create(['tenant_id' => $this->tenant->id, 'nome' => 'Cliente Sem Placa']);

        $this->actingAs($this->adminUser)
            ->postJson('/json/veiculos', [
                'cliente_id' => $cliente->id,
                'modelo'     => 'Sem Placa',
            ])
            ->assertOk();

        $this->assertDatabaseHas('veiculos', [
            'cliente_id' => $cliente->id,
            'placa'      => null,
        ]);
    }
}
