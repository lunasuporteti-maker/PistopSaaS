<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parceiro extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = ['tenant_id', 'nome', 'servico_prestado', 'telefone', 'ativo'];

    protected $casts = ['ativo' => 'boolean'];

    public function pagamentosSaida()
    {
        return $this->hasMany(PagamentoSaida::class);
    }
}
