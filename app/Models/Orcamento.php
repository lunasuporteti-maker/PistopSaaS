<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orcamento extends Model
{
    use BelongsToTenant, SoftDeletes;

    // Canais de aprovação
    public const CANAL_PORTAL = 'portal';

    public const CANAL_INTERNO = 'interno';

    public const CANAL_WHATSAPP = 'whatsapp';

    protected $fillable = [
        'tenant_id', 'cliente_id', 'veiculo_id', 'status', 'observacao',
        'valor_total', 'posicao_fila', 'km_entrada',
        'queixa_cliente', 'parecer_tecnico', 'andamento',
        'aprovado_em', 'iniciado_em', 'concluido_em', 'arquivado_em', 'token_publico',
        'aprovado_por_canal', 'aprovado_ip', 'aprovado_user_agent',
    ];

    protected $casts = [
        'aprovado_em' => 'datetime',
        'iniciado_em' => 'datetime',
        'concluido_em' => 'datetime',
        'arquivado_em' => 'datetime',
        'valor_total' => 'decimal:2',
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

    public function interacoes()
    {
        return $this->hasMany(OrcamentoInteracao::class);
    }

    public function fotos()
    {
        return $this->hasMany(ServicoFoto::class);
    }
}
