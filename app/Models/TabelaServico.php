<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TabelaServico extends Model
{
    use SoftDeletes;

    protected $table = 'tabela_servicos';

    protected $fillable = [
        'nome', 'tempo_estimado_horas',
        'preco_mao_de_obra', 'margem_lucro_percent', 'ativo',
    ];

    protected $casts = [
        'tempo_estimado_horas' => 'decimal:2',
        'preco_mao_de_obra'    => 'decimal:2',
        'margem_lucro_percent' => 'decimal:2',
        'ativo'                => 'boolean',
    ];
}
