<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Funcionario extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = ['tenant_id', 'nome', 'cargo', 'salario_base', 'telefone', 'ativo'];

    protected $casts = [
        'salario_base' => 'decimal:2',
        'ativo'        => 'boolean',
    ];

    public function pagamentosSaida()
    {
        return $this->hasMany(PagamentoSaida::class);
    }

    public function comissoes()
    {
        return $this->hasMany(Comissao::class);
    }
}
