<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicoFoto extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'servico_fotos';

    // Categorias lógicas de foto
    public const CATEGORIA_ANTES   = 'antes';
    public const CATEGORIA_DURANTE = 'durante';
    public const CATEGORIA_DEPOIS  = 'depois';
    public const CATEGORIA_PECA    = 'peca';
    public const CATEGORIA_OUTRO   = 'outro';

    protected $fillable = [
        'tenant_id',
        'orcamento_id',
        'ordem_servico_id',
        'categoria',
        'legenda',
        'path_original',
        'path_thumbnail',
        'tamanho_bytes',
        'mime_type',
        'uploaded_by',
    ];

    protected $casts = [
        'tamanho_bytes' => 'integer',
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
