<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntradaEstoque extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'entradas_estoque';

    protected $fillable = [
        'tenant_id',
        'numero_entrada',
        'fornecedor_id',
        'data_entrada',
        'numero_nota',
        'tipo_documento',
        'valor_total',
        'status',
        'observacoes',
        'anexo_path',
        'cancelado_por',
        'cancelado_em',
        'cancelado_motivo',
        'usuario_id',
    ];

    protected $casts = [
        'valor_total'  => 'decimal:2',
        'data_entrada' => 'date',
        'cancelado_em' => 'datetime',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relacionamentos
    // ─────────────────────────────────────────────────────────────

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function canceladoPorUsuario()
    {
        return $this->belongsTo(User::class, 'cancelado_por');
    }

    public function itens()
    {
        return $this->hasMany(EntradaEstoqueItem::class, 'entrada_id');
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('status', 'ativa');
    }

    public function scopeCanceladas(Builder $query): Builder
    {
        return $query->where('status', 'cancelada');
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    public function isCancelada(): bool
    {
        return $this->status === 'cancelada';
    }
}
