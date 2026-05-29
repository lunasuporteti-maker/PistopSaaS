<?php

namespace App\Services;

use App\Models\Tenant;

class TrialLimitService
{
    // Limites do período trial — não se aplicam a legados nem a plano pago
    public const LIMITES = [
        'clientes'   => 10,
        'veiculos'   => 20,
        'orcamentos' => 15,
        'usuarios'   => 2,
        'pecas'      => 20,
    ];

    public function tenantEmTrial(Tenant $tenant): bool
    {
        // Legado (trial_ends_at = null) → sem limitação
        if ($tenant->trial_ends_at === null) {
            return false;
        }
        // Plano pago ativo → sem limitação
        if ($tenant->emDia()) {
            return false;
        }
        // Trial ativo (mesmo que expirado — read-only aplica, não limite de criação)
        return $tenant->trialAtivo();
    }

    public function atingiuLimite(string $recurso, Tenant $tenant): bool
    {
        if (! $this->tenantEmTrial($tenant)) {
            return false;
        }

        $limite = self::LIMITES[$recurso] ?? null;
        if ($limite === null) {
            return false;
        }

        $count = match ($recurso) {
            'clientes'   => \App\Models\Cliente::count(),
            'veiculos'   => \App\Models\Veiculo::count(),
            'orcamentos' => \App\Models\Orcamento::count(),
            'usuarios'   => \App\Models\User::where('perfil', '!=', 'super_admin')->count(),
            'pecas'      => \App\Models\Peca::count(),
            default      => 0,
        };

        return $count >= $limite;
    }

    public function mensagemLimite(string $recurso): string
    {
        $limite  = self::LIMITES[$recurso] ?? '?';
        $nomes   = [
            'clientes'   => 'clientes',
            'veiculos'   => 'veículos',
            'orcamentos' => 'orçamentos',
            'usuarios'   => 'usuários',
            'pecas'      => 'peças',
        ];
        $nome = $nomes[$recurso] ?? $recurso;

        return "Limite do trial atingido: máximo de {$limite} {$nome}. Assine o Plano Pro para continuar.";
    }
}
