<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdemServico extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'ordens_servico';

    protected $fillable = [
        'tenant_id', 'numero_os', 'orcamento_id', 'cliente_id', 'veiculo_id',
        'descricao', 'valor_total', 'garantia_dias', 'finalizado_em',
    ];

    protected $casts = [
        'valor_total'   => 'decimal:2',
        'finalizado_em' => 'datetime',
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function pecas()
    {
        return $this->hasMany(OsPeca::class, 'os_id');
    }

    public function pagamentos()
    {
        return $this->hasMany(PagamentoOs::class, 'os_id');
    }

    public function comissoes()
    {
        return $this->hasMany(Comissao::class, 'os_id');
    }

    public function lembretes()
    {
        return $this->hasMany(Lembrete::class, 'os_id');
    }

    public function financeiro()
    {
        return $this->hasMany(Financeiro::class, 'os_id');
    }

    public static function gerarNumeroOs(): string
    {
        $ultimo = static::withTrashed()->max('id') ?? 0;
        return 'OS' . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);
    }
}
