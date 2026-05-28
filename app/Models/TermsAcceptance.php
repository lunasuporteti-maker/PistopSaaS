<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Registro imutável de aceite de termos / consentimentos (PRD 03, LGPD).
 *
 * Insert-only: sem updated_at. Pode estar associado a um signup (pré-tenant)
 * e/ou a um tenant/user já criados.
 */
class TermsAcceptance extends Model
{
    use HasFactory;

    // Tabela insert-only: não há coluna updated_at
    public const UPDATED_AT = null;

    // Tipos de aceite
    public const TIPO_TERMOS_USO = 'termos_uso';

    public const TIPO_PRIVACIDADE = 'privacidade';

    public const TIPO_MARKETING = 'marketing';

    protected $fillable = [
        'tenant_signup_id',
        'tenant_id',
        'user_id',
        'tipo',
        'versao',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function tenantSignup()
    {
        return $this->belongsTo(TenantSignup::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
