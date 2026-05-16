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
        'aberto_por_user_id', 'fechado_por_user_id',
    ];

    protected $casts = [
        'data'          => 'date',
        'saldo_inicial' => 'decimal:2',
        'saldo_final'   => 'decimal:2',
        'aberto_em'     => 'datetime',
        'fechado_em'    => 'datetime',
    ];

    public function abertoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'aberto_por_user_id');
    }

    public function fechadoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'fechado_por_user_id');
    }

    public static function caixaAberto(): ?static
    {
        return static::where('status', 'aberto')
            ->whereDate('data', today())
            ->first();
    }
}
