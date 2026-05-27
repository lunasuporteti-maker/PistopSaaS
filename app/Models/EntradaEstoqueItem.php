<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntradaEstoqueItem extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'entradas_estoque_itens';

    protected $fillable = [
        'tenant_id',
        'entrada_id',
        'peca_id',
        'quantidade',
        'preco_custo_unitario',
        'subtotal',
    ];

    protected $casts = [
        'preco_custo_unitario' => 'decimal:2',
        'subtotal'             => 'decimal:2',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relacionamentos
    // ─────────────────────────────────────────────────────────────

    public function entrada()
    {
        return $this->belongsTo(EntradaEstoque::class, 'entrada_id');
    }

    public function peca()
    {
        return $this->belongsTo(Peca::class);
    }
}
