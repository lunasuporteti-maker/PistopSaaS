<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OsPeca extends Model
{
    protected $table = 'os_pecas';

    protected $fillable = ['os_id', 'peca_id', 'quantidade', 'preco_unitario'];

    protected $casts = ['preco_unitario' => 'decimal:2'];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'os_id');
    }

    public function peca()
    {
        return $this->belongsTo(Peca::class);
    }
}
