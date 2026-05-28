<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Signup de onboarding self-service (PRD 03).
 *
 * NÃO usa BelongsToTenant: o signup acontece ANTES do tenant existir,
 * portanto não deve ser filtrado pelo escopo de tenant atual.
 */
class TenantSignup extends Model
{
    use HasFactory;

    // Planos disponíveis
    public const PLANO_TRIAL = 'trial';

    public const PLANO_PADRAO = 'padrao';

    // Status do signup
    public const STATUS_PENDING = 'pending_email_confirmation';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_ABANDONED = 'abandoned';

    protected $fillable = [
        'nome_oficina',
        'slug_desejado',
        'cnpj',
        'telefone',
        'cidade',
        'uf',
        'nome_completo',
        'email',
        'senha_hash',
        'plano_escolhido',
        'consentimento_emails_transacionais',
        'consentimento_marketing',
        'token_confirmacao',
        'token_expira_em',
        'status',
        'tenant_id',
        'ip_origem',
        'user_agent',
    ];

    protected $casts = [
        'consentimento_emails_transacionais' => 'boolean',
        'consentimento_marketing' => 'boolean',
        'token_expira_em' => 'datetime',
    ];

    protected $hidden = [
        'senha_hash',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function termsAcceptances()
    {
        return $this->hasMany(TermsAcceptance::class);
    }
}
