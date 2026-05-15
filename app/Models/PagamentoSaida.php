<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PagamentoSaida extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'pagamentos_saida';

    protected $fillable = [
        'tenant_id', 'tipo', 'descricao', 'valor',
        'funcionario_id', 'parceiro_id',
        'data_pagamento', 'mes_referencia', 'categoria',
    ];

    protected $casts = [
        'valor'          => 'decimal:2',
        'data_pagamento' => 'datetime',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function parceiro()
    {
        return $this->belongsTo(Parceiro::class);
    }
}
