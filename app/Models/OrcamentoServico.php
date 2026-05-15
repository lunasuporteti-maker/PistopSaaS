<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrcamentoServico extends Model
{
    protected $table = 'orcamento_servicos';

    protected $fillable = ['orcamento_id', 'servico_nome', 'valor'];

    protected $casts = ['valor' => 'decimal:2'];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }
}
