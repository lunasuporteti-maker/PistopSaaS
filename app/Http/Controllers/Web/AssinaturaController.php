<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Orcamento;
use App\Models\Peca;
use App\Models\SubscriptionLog;
use App\Models\User;
use App\Services\AsaasService;
use App\Services\TrialLimitService;
use Illuminate\Support\Carbon;

class AssinaturaController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');
        $service = app(TrialLimitService::class);

        $logs = SubscriptionLog::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $uso = null;
        if ($service->tenantEmTrial($tenant)) {
            $uso = [
                'clientes' => ['atual' => Cliente::count(),   'limite' => TrialLimitService::LIMITES['clientes']],
                'orcamentos' => ['atual' => Orcamento::count(), 'limite' => TrialLimitService::LIMITES['orcamentos']],
                'usuarios' => ['atual' => User::where('perfil', '!=', 'super_admin')->count(), 'limite' => TrialLimitService::LIMITES['usuarios']],
                'pecas' => ['atual' => Peca::count(),      'limite' => TrialLimitService::LIMITES['pecas']],
            ];
        }

        // ── Status estendido: validade, dias restantes e badge ────────────────
        $dataValidade = $tenant->trialAtivo() ? $tenant->trial_ends_at : $tenant->plano_vence_em;

        $validade = $dataValidade ? Carbon::parse($dataValidade)->format('d/m/Y') : null;
        $diasRestantes = $dataValidade
            ? (int) now()->startOfDay()->diffInDays(Carbon::parse($dataValidade)->startOfDay(), false)
            : null;

        $statusBadge = 'success';
        if ($diasRestantes !== null) {
            if ($diasRestantes < 0) {
                $statusBadge = 'danger';
            } elseif ($diasRestantes <= 5) {
                $statusBadge = 'warning';
            }
        }

        // ── Cobranças na Asaas + fallback/degradação graciosa (Story 6.4) ─────
        // Prioridade: legado (sem customer_id) > API indisponível (null) > normal
        $pendentes = [];
        $historico = [];
        $tenantLegado = ! $tenant->subscription || ! $tenant->subscription->gateway_customer_id;
        $asaasIndisponivel = false;

        if (! $tenantLegado) {
            $asaas = app(AsaasService::class);
            $customerId = $tenant->subscription->gateway_customer_id;

            // ?array: null = falha de comunicação (timeout/5xx), [] = sem dados
            $pendentesResult = $asaas->pagamentosPendentes($customerId);
            $historicoResult = $asaas->listarPagamentos($customerId, ['limit' => 12]);

            if ($pendentesResult === null || $historicoResult === null) {
                $asaasIndisponivel = true;
                // Indisponível: não exibe tabelas vazias, apenas o aviso
                $pendentes = [];
                $historico = [];
            } else {
                $pendentes = $pendentesResult;
                $historico = $historicoResult;
                usort($historico, fn ($a, $b) => strcmp($b['dueDate'] ?? '', $a['dueDate'] ?? ''));
            }
        }

        $mapStatus = fn (string $status): array => $this->mapStatusAsaas($status);

        return view('pitstop.assinatura', compact(
            'tenant', 'logs', 'uso',
            'pendentes', 'historico', 'mapStatus',
            'tenantLegado', 'asaasIndisponivel',
            'validade', 'diasRestantes', 'statusBadge'
        ));
    }

    /** Mapeia status Asaas → rótulo + classe de badge (FR-057) */
    private function mapStatusAsaas(string $status): array
    {
        return match ($status) {
            'CONFIRMED', 'RECEIVED', 'RECEIVED_IN_CASH' => ['label' => 'Pago', 'badge' => 'success'],
            'PENDING' => ['label' => 'Pendente', 'badge' => 'warning'],
            'OVERDUE' => ['label' => 'Atrasado', 'badge' => 'danger'],
            'REFUNDED', 'REFUND_REQUESTED' => ['label' => 'Estornado', 'badge' => 'secondary'],
            'DELETED' => ['label' => 'Cancelado', 'badge' => 'secondary'],
            default => ['label' => 'Em processamento', 'badge' => 'light'],
        };
    }
}
