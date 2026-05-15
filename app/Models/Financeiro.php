<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Financeiro extends Model
{
    protected $table = 'financeiro';

    protected $fillable = [
        'os_id', 'tipo', 'descricao', 'valor', 'data_pagamento',
    ];

    protected $casts = [
        'valor'          => 'decimal:2',
        'data_pagamento' => 'datetime',
    ];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'os_id');
    }
}
