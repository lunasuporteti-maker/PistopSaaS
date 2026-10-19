<?php

namespace Tests\Unit\Models;

use App\Models\Veiculo;
use Tests\TestCase;

/**
 * Story "Busca Interna por Placa" — normalizador e portabilidade do scope.
 * Cobre AC1 (normalização) e a portabilidade Postgres/SQLite do scope (AC2/AC10),
 * sem tocar o banco (função pura + inspeção do SQL gerado).
 */
class VeiculoPlacaTest extends TestCase
{
    // ── AC1: normalizarPlaca ────────────────────────────────────────────────

    public function test_normaliza_remove_hifen_espaco_e_maiusculas(): void
    {
        $this->assertSame('ABC1234', Veiculo::normalizarPlaca('abc-1234'));
        $this->assertSame('ABC1D23', Veiculo::normalizarPlaca('ABC 1D23'));
        $this->assertSame('ABC1D23', Veiculo::normalizarPlaca('  abc1d23 '));
        $this->assertSame('ABC1234', Veiculo::normalizarPlaca('abc.1234'));
    }

    public function test_normaliza_cobre_formato_antigo_e_mercosul(): void
    {
        $this->assertSame('ABC1234', Veiculo::normalizarPlaca('ABC-1234')); // antigo
        $this->assertSame('ABC1D23', Veiculo::normalizarPlaca('abc1d23'));  // Mercosul
    }

    public function test_normaliza_vazio_ou_null_retorna_string_vazia(): void
    {
        $this->assertSame('', Veiculo::normalizarPlaca(null));
        $this->assertSame('', Veiculo::normalizarPlaca(''));
        $this->assertSame('', Veiculo::normalizarPlaca('   '));
        $this->assertSame('', Veiculo::normalizarPlaca('---'));
    }

    // ── AC2/AC10: portabilidade do SQL (sem ilike, só UPPER/REPLACE) ─────────

    public function test_expr_placa_normalizada_so_usa_upper_e_replace(): void
    {
        $expr = Veiculo::exprPlacaNormalizada();

        $this->assertStringContainsString('UPPER', $expr);
        $this->assertStringContainsString('REPLACE', $expr);
        $this->assertStringNotContainsStringIgnoringCase('ilike', $expr);
    }

    public function test_scope_por_placa_gera_sql_portavel_sem_ilike(): void
    {
        $sql = Veiculo::porPlaca('ABC-1234')->toSql();

        $this->assertStringNotContainsStringIgnoringCase('ilike', $sql);
        $this->assertStringContainsString('UPPER', $sql);
        $this->assertStringContainsString('REPLACE', $sql);
    }

    public function test_scope_por_placa_vazia_nao_retorna_a_base(): void
    {
        $sql = Veiculo::porPlaca('')->toSql();

        $this->assertStringContainsString('1 = 0', $sql);
    }
}
