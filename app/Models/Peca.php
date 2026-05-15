<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Peca extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'nome', 'quantidade', 'preco_custo', 'preco_venda', 'estoque_minimo',
    ];

    protected $casts = [
        'preco_custo'  => 'decimal:2',
        'preco_venda'  => 'decimal:2',
    ];

    public function orcamentoPecas()
    {
        return $this->hasMany(OrcamentoPeca::class);
    }

    public function osPecas()
    {
        return $this->hasMany(OsPeca::class);
    }

    public function estoqueAbaixoMinimo(): bool
    {
        return $this->quantidade <= $this->estoque_minimo;
    }
}
