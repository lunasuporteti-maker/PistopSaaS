<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Veiculo extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'cliente_id', 'marca', 'modelo', 'ano',
        'placa', 'cor', 'km_atual',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function orcamentos()
    {
        return $this->hasMany(Orcamento::class);
    }

    public function ordensServico()
    {
        return $this->hasMany(OrdemServico::class);
    }

    public function historicoKm()
    {
        return $this->hasMany(HistoricoKm::class);
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }

    public function lembretes()
    {
        return $this->hasMany(Lembrete::class);
    }
}
