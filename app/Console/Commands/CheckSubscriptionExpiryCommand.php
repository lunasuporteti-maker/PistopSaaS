<?php

namespace App\Console\Commands;

use App\Mail\PlanoExpirando1DiaMail;
use App\Mail\PlanoExpirando3DiasMail;
use App\Mail\PlanoExpirando7DiasMail;
use App\Models\SubscriptionLog;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckSubscriptionExpiryCommand extends Command
{
    protected $signature   = 'pitstop:check-subscription-expiry';
    protected $description = 'Envia emails de alerta de vencimento de plano pago (D-7, D-3, D-1)';

    public function handle(): int
    {
        $tenants = Tenant::where('plano_ativo', true)
            ->whereNotNull('plano_vence_em')
            ->get();

        foreach ($tenants as $tenant) {
            $dias = (int) now()->startOfDay()->diffInDays(
                $tenant->plano_vence_em->startOfDay(),
                false
            );

            if ($dias === 7 && ! $this->jaEnviou($tenant->id, 'email_plano_expirando_7_dias')) {
                $this->enviarEmail($tenant, 'email_plano_expirando_7_dias', new PlanoExpirando7DiasMail($tenant));
            }

            if ($dias === 3 && ! $this->jaEnviou($tenant->id, 'email_plano_expirando_3_dias')) {
                $this->enviarEmail($tenant, 'email_plano_expirando_3_dias', new PlanoExpirando3DiasMail($tenant));
            }

            if ($dias === 1 && ! $this->jaEnviou($tenant->id, 'email_plano_expirando_1_dia')) {
                $this->enviarEmail($tenant, 'email_plano_expirando_1_dia', new PlanoExpirando1DiaMail($tenant));
            }
        }

        $this->info('Verificação de vencimento de plano concluída.');
        return Command::SUCCESS;
    }

    private function jaEnviou(int $tenantId, string $evento): bool
    {
        return SubscriptionLog::where('tenant_id', $tenantId)
            ->where('evento', $evento)
            ->where('created_at', '>=', now()->subDays(30))
            ->exists();
    }

    private function enviarEmail(Tenant $tenant, string $evento, object $mailable): void
    {
        $adminEmail = $tenant->users()->where('perfil', 'admin')->value('email');

        if (! $adminEmail) {
            return;
        }

        try {
            Mail::to($adminEmail)->queue($mailable);

            SubscriptionLog::create([
                'tenant_id'    => $tenant->id,
                'evento'       => $evento,
                'payload_json' => json_encode([
                    'sent_at'   => now()->toIso8601String(),
                    'email'     => $adminEmail,
                    'vence_em'  => $tenant->plano_vence_em->toDateString(),
                ]),
            ]);
        } catch (\Throwable $e) {
            Log::error("CheckSubscriptionExpiry: erro ao enviar {$evento} para tenant {$tenant->id}: {$e->getMessage()}");
        }
    }
}
