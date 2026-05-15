<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = ['tenant_id', 'nome', 'telefone', 'email', 'cpf', 'endereco'];

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class);
    }

    public function orcamentos()
    {
        return $this->hasMany(Orcamento::class);
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }

    public function lembretes()
    {
        return $this->hasMany(Lembrete::class);
    }

    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class);
    }
}
