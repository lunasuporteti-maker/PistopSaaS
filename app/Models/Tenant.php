<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'slug', 'dominio_customizado',
        'plano', 'ativo', 'observacao',
        'trial_ends_at', 'plano_ativo', 'plano_vence_em',
    ];

    protected $casts = [
        'ativo'          => 'boolean',
        'plano_ativo'    => 'boolean',
        'trial_ends_at'  => 'datetime',
        'plano_vence_em' => 'date',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    // ── Helpers de assinatura ─────────────────────────────────────────────────

    /** Trial ativo: trial_ends_at definido e ainda não venceu */
    public function trialAtivo(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    /** Dias restantes do trial (0 se expirado ou sem trial) */
    public function diasTrialRestantes(): int
    {
        if (! $this->trialAtivo()) {
            return 0;
        }
        return (int) now()->diffInDays($this->trial_ends_at, false);
    }

    /** Plano pago em dia: plano_ativo = true E (plano_vence_em nulo OU não venceu) */
    public function emDia(): bool
    {
        if (! $this->plano_ativo) {
            return false;
        }
        if ($this->plano_vence_em === null) {
            return true; // plano vitalício / manual
        }
        return $this->plano_vence_em->isFuture();
    }

    /** Acesso permitido: trial ativo OU plano em dia */
    public function acessoPermitido(): bool
    {
        // Tenants legados (sem trial_ends_at e sem plano) → acesso livre
        if ($this->trial_ends_at === null && ! $this->plano_ativo) {
            return true;
        }
        return $this->trialAtivo() || $this->emDia();
    }

    /** Status legível para o painel admin */
    public function statusAssinatura(): string
    {
        if ($this->emDia()) {
            return 'Plano Ativo';
        }
        if ($this->trialAtivo()) {
            return 'Trial (' . $this->diasTrialRestantes() . ' dias)';
        }
        if ($this->trial_ends_at === null && ! $this->plano_ativo) {
            return 'Legado';
        }
        return 'Expirado';
    }

    // ── Resolve tenant pelo subdomain ou domínio customizado ─────────────────
    public static function resolverPorHost(string $host): ?static
    {
        // Domínio customizado exato
        $tenant = static::where('dominio_customizado', $host)->where('ativo', true)->first();
        if ($tenant) {
            return $tenant;
        }

        // Extrai subdomínio: "pitstop.pitstop.com.br" → "pitstop"
        $partes = explode('.', $host);
        if (count($partes) >= 3) {
            $slug = $partes[0];
            return static::where('slug', $slug)->where('ativo', true)->first();
        }

        return null;
    }
}
