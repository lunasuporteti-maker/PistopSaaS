<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrcamentoMaoDeObra extends Model
{
    protected $table = 'orcamento_mao_de_obra';

    protected $fillable = ['orcamento_id', 'mao_de_obra_id', 'nome_custom', 'valor'];

    protected $casts = ['valor' => 'decimal:2'];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function maoDeObra()
    {
        return $this->belongsTo(MaoDeObra::class, 'mao_de_obra_id');
    }
}
