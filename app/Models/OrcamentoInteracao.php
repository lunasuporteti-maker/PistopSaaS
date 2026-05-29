<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class OrcamentoInteracao extends Model
{
    use BelongsToTenant;

    protected $table = 'orcamento_interacoes';

    // Insert-only / evidência legal imutável: só created_at, sem updated_at.
    public const UPDATED_AT = null;

    // Tipos lógicos de interação
    public const TIPO_VISUALIZACAO = 'visualizacao';

    public const TIPO_APROVACAO = 'aprovacao';

    public const TIPO_REJEICAO = 'rejeicao';

    public const TIPO_REVISAO_VALOR = 'revisao_valor';

    public const TIPO_UPLOAD_FOTO = 'upload_foto';

    public const TIPO_EXCLUSAO_FOTO = 'exclusao_foto';

    protected $fillable = [
        'tenant_id',
        'orcamento_id',
        'tipo',
        'dados_json',
        'usuario_id',
    ];

    protected $casts = [
        'dados_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
