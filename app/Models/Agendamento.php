<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agendamento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cliente_id', 'veiculo_id', 'data_hora',
        'servico', 'status', 'observacao', 'resultado',
    ];

    protected $casts = ['data_hora' => 'datetime'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }
}
