<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricoKm extends Model
{
    protected $table = 'historico_km';

    protected $fillable = ['veiculo_id', 'km', 'observacao'];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }
}
