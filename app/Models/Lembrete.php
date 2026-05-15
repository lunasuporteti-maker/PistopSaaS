<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Lembrete extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'cliente_id', 'veiculo_id', 'os_id',
        'servico_nome', 'data_servico', 'data_lembrete', 'status',
    ];

    protected $casts = [
        'data_servico'  => 'datetime',
        'data_lembrete' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'os_id');
    }
}
