<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrcamentoPeca extends Model
{
    protected $table = 'orcamento_pecas';

    protected $fillable = ['orcamento_id', 'peca_id', 'quantidade', 'preco_unitario'];

    protected $casts = ['preco_unitario' => 'decimal:2'];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function peca()
    {
        return $this->belongsTo(Peca::class);
    }
}
