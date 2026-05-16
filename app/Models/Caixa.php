<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Caixa extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'data', 'saldo_inicial', 'saldo_final',
        'observacao_abertura', 'observacao_fechamento',
        'status', 'aberto_em', 'fechado_em',
    ];

    protected $casts = [
        'data'          => 'date',
        'saldo_inicial' => 'decimal:2',
        'saldo_final'   => 'decimal:2',
        'aberto_em'     => 'datetime',
        'fechado_em'    => 'datetime',
    ];

    public static function caixaAberto(): ?static
    {
        return static::where('status', 'aberto')
            ->whereDate('data', today())
            ->first();
    }
}
