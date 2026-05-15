<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comissao extends Model
{
    protected $fillable = [
        'funcionario_id', 'os_id',
        'percentual', 'valor', 'data_pagamento', 'pago',
    ];

    protected $casts = [
        'percentual'     => 'decimal:2',
        'valor'          => 'decimal:2',
        'data_pagamento' => 'datetime',
        'pago'           => 'boolean',
    ];

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'os_id');
    }
}
