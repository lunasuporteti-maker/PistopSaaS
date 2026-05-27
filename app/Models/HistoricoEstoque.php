<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoEstoque extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'historico_estoque';

    // Append-only — sem updated_at
    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'tenant_id',
        'peca_id',
        'tipo',
        'quantidade_antes',
        'quantidade_depois',
        'quantidade_delta',
        'referencia_tipo',
        'referencia_id',
        'usuario_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relacionamentos
    // ─────────────────────────────────────────────────────────────

    public function peca()
    {
        return $this->belongsTo(Peca::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
