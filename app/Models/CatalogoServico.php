<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatalogoServico extends Model
{
    use SoftDeletes;

    protected $table = 'catalogo_servicos';

    protected $fillable = [
        'nome', 'descricao', 'preco_sugerido',
        'tempo_estimado_horas', 'dias_lembrete', 'ativo',
    ];

    protected $casts = [
        'preco_sugerido'        => 'decimal:2',
        'tempo_estimado_horas'  => 'decimal:2',
        'ativo'                 => 'boolean',
    ];
}
