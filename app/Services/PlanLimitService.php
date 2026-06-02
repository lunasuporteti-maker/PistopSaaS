<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;

class PlanLimitService
{
    // Máximo de usuários por tier de plano pago
    public const LIMITE_USUARIOS = [
        'pro'     => 6,
        'pro_max' => 10,
    ];

    /**
     * Retorna true se o tenant em plano pago atingiu o limite de usuários.
     * Não se aplica a trial (responsabilidade do TrialLimitService).
     */
    public function atingiuLimiteUsuarios(Tenant $tenant): bool
    {
        if (! $tenant->emDia()) {
            return false;
        }

        $limite = self::LIMITE_USUARIOS[$tenant->tier()] ?? null;
        if ($limite === null) {
            return false;
        }

        $count = User::where('tenant_id', $tenant->id)
            ->where('perfil', '!=', 'super_admin')
            ->count();

        return $count >= $limite;
    }

    /**
     * Quantos usuários o tenant pode criar ainda.
     * Retorna null se não há limite definido para o tier.
     */
    public function vagasRestantes(Tenant $tenant): ?int
    {
        if (! $tenant->emDia()) {
            return null;
        }

        $limite = self::LIMITE_USUARIOS[$tenant->tier()] ?? null;
        if ($limite === null) {
            return null;
        }

        $count = User::where('tenant_id', $tenant->id)
            ->where('perfil', '!=', 'super_admin')
            ->count();

        return max(0, $limite - $count);
    }

    /**
     * Informações de uso do slot de usuários para exibir na UI.
     * Retorna null se não houver limite aplicável.
     */
    public function infoSlots(Tenant $tenant): ?array
    {
        // Trial: usa TrialLimitService — não é responsabilidade desta classe
        if ($tenant->trialAtivo()) {
            $limite = TrialLimitService::LIMITES['usuarios'] ?? 2;
            $count  = User::where('tenant_id', $tenant->id)
                ->where('perfil', '!=', 'super_admin')
                ->count();

            return [
                'atual'  => $count,
                'limite' => $limite,
                'label'  => "Trial ({$count}/{$limite} usuários)",
                'cor'    => $count >= $limite ? 'danger' : ($count >= $limite - 1 ? 'warning' : 'success'),
            ];
        }

        if (! $tenant->emDia()) {
            return null;
        }

        $limite = self::LIMITE_USUARIOS[$tenant->tier()] ?? null;
        if ($limite === null) {
            return null;
        }

        $count = User::where('tenant_id', $tenant->id)
            ->where('perfil', '!=', 'super_admin')
            ->count();

        return [
            'atual'  => $count,
            'limite' => $limite,
            'label'  => "{$count}/{$limite} usuários — " . $tenant->nomePlano(),
            'cor'    => $count >= $limite ? 'danger' : ($count >= $limite - 1 ? 'warning' : 'success'),
        ];
    }

    public function mensagemLimite(Tenant $tenant): string
    {
        $limite = self::LIMITE_USUARIOS[$tenant->tier()] ?? '?';

        return "Limite do {$tenant->nomePlano()} atingido: máximo de {$limite} usuários. Entre em contato para fazer upgrade.";
    }
}
