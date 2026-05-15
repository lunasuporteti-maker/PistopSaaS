<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Financeiro extends Model
{
    use BelongsToTenant;

    protected $table = 'financeiro';

    protected $fillable = [
        'tenant_id', 'os_id', 'tipo', 'descricao', 'valor', 'data_pagamento',
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
