<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Veiculo extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'is_example', 'cliente_id', 'marca', 'modelo', 'ano',
        'placa', 'cor', 'km_atual', 'tipo_veiculo',
    ];

    /**
     * Normaliza uma placa para comparação/gravação: maiúsculas e sem qualquer
     * caractere não alfanumérico (hífen, espaço, ponto etc.).
     * Cobre formato antigo (ABC1234) e Mercosul (ABC1D23).
     * `null`/vazio → ''.
     */
    public static function normalizarPlaca(?string $valor): string
    {
        if ($valor === null) {
            return '';
        }

        return preg_replace('/[^A-Z0-9]/', '', strtoupper($valor)) ?? '';
    }

    /**
     * Expressão SQL portável (PostgreSQL + SQLite) que normaliza a COLUNA placa
     * no lado do banco, cobrindo registros legados gravados sujos
     * (minúsculas/hífen/espaço). Usa só UPPER/REPLACE — sem ilike.
     * Fonte única usada pelo scope, pelo index e pela busca global.
     */
    public static function exprPlacaNormalizada(): string
    {
        return "REPLACE(REPLACE(REPLACE(UPPER(placa), '-', ''), ' ', ''), '.', '')";
    }

    /**
     * Busca por placa normalizando os dois lados (termo e coluna).
     * $exata = true → igualdade; false → LIKE %termo% (parcial).
     * Portável entre PostgreSQL (produção) e SQLite (testes).
     */
    public function scopePorPlaca(Builder $query, ?string $termo, bool $exata = true): Builder
    {
        $normalizada = static::normalizarPlaca($termo);

        // Termo vazio não deve retornar a base inteira.
        if ($normalizada === '') {
            return $query->whereRaw('1 = 0');
        }

        $expr = static::exprPlacaNormalizada();

        return $exata
            ? $query->whereRaw("{$expr} = ?", [$normalizada])
            : $query->whereRaw("{$expr} LIKE ?", ['%' . $normalizada . '%']);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function orcamentos()
    {
        return $this->hasMany(Orcamento::class);
    }

    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class);
    }

    public function historicoKm()
    {
        return $this->hasMany(HistoricoKm::class);
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }

    public function lembretes()
    {
        return $this->hasMany(Lembrete::class);
    }
}
