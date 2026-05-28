<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Peca extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'is_example', 'nome', 'quantidade', 'preco_custo', 'preco_venda', 'estoque_minimo',
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

    public function entradaEstoqueItens()
    {
        return $this->hasMany(EntradaEstoqueItem::class);
    }

    public function historicoEstoque()
    {
        return $this->hasMany(HistoricoEstoque::class);
    }

    public function estoqueAbaixoMinimo(): bool
    {
        return $this->quantidade <= $this->estoque_minimo;
    }
}
