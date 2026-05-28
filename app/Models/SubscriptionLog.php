<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Log insert-only de eventos de assinatura (PRD 03).
 *
 * Sem updated_at por design — usamos apenas created_at (CREATED_AT/UPDATED_AT
 * desativado para updated_at). payload_json armazenado como texto.
 */
class SubscriptionLog extends Model
{
    use HasFactory;

    // Tabela insert-only: não há coluna updated_at
    public const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'evento',
        'payload_json',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
