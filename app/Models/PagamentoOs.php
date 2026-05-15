<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagamentoOs extends Model
{
    protected $table = 'pagamentos_os';

    protected $fillable = ['os_id', 'forma', 'valor'];

    protected $casts = ['valor' => 'decimal:2'];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'os_id');
    }
}
