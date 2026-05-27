<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fornecedor extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    // Laravel pluraliza como 'fornecedors' em inglês — forçar nome correto
    protected $table = 'fornecedores';

    protected $fillable = [
        'tenant_id', 'nome', 'cnpj', 'telefone', 'email',
        'endereco', 'observacoes', 'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function entradasEstoque()
    {
        return $this->hasMany(EntradaEstoque::class);
    }
}
