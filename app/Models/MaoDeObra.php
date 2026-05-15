<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaoDeObra extends Model
{
    use SoftDeletes;

    protected $table = 'mao_de_obra';

    protected $fillable = [
        'nome', 'descricao', 'preco', 'tempo_estimado_horas', 'ativo',
    ];

    protected $casts = [
        'preco'                 => 'decimal:2',
        'tempo_estimado_horas'  => 'decimal:2',
        'ativo'                 => 'boolean',
    ];

    public function orcamentoItens()
    {
        return $this->hasMany(OrcamentoMaoDeObra::class, 'mao_de_obra_id');
    }
}
