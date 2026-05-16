<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orcamento extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'cliente_id', 'veiculo_id', 'status', 'observacao',
        'valor_total', 'posicao_fila', 'km_entrada',
        'queixa_cliente', 'parecer_tecnico',
        'aprovado_em', 'iniciado_em', 'concluido_em', 'arquivado_em',
    ];

    protected $casts = [
        'aprovado_em'   => 'datetime',
        'iniciado_em'   => 'datetime',
        'concluido_em'  => 'datetime',
        'arquivado_em'  => 'datetime',
        'valor_total'   => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function servicos()
    {
        return $this->hasMany(OrcamentoServico::class);
    }

    public function pecas()
    {
        return $this->hasMany(OrcamentoPeca::class);
    }

    public function maoDeObra()
    {
        return $this->hasMany(OrcamentoMaoDeObra::class);
    }

    public function ordemServico()
    {
        return $this->hasOne(OrdemServico::class);
    }
}
