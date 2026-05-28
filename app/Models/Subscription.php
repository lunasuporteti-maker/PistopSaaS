<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Assinatura por tenant (PRD 03). 1 por tenant.
 *
 * NÃO usa BelongsToTenant: super admin e webhooks de gateway precisam acessar
 * assinaturas de qualquer tenant. O controle de acesso é feito na camada de
 * controller/middleware.
 */
class Subscription extends Model
{
    use HasFactory;

    // Planos
    public const PLANO_TRIAL = 'trial';

    public const PLANO_PADRAO = 'padrao';

    // Status
    public const STATUS_TRIAL = 'trial';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAST_DUE = 'past_due';

    public const STATUS_CANCELED = 'canceled';

    public const STATUS_EXPIRED = 'expired';

    // Gateways
    public const GATEWAY_ASAAS = 'asaas';

    public const GATEWAY_MANUAL = 'manual';

    protected $fillable = [
        'tenant_id',
        'plano',
        'status',
        'trial_termina_em',
        'proximo_vencimento',
        'gateway',
        'gateway_subscription_id',
        'gateway_customer_id',
    ];

    protected $casts = [
        'trial_termina_em' => 'datetime',
        'proximo_vencimento' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
