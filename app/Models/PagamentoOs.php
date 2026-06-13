<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PagamentoOs extends Model
{
    use BelongsToTenant;

    protected $table = 'pagamentos_os';

    protected $fillable = ['os_id', 'forma', 'valor'];

    protected $casts = ['valor' => 'decimal:2'];

    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'os_id');
    }
}
