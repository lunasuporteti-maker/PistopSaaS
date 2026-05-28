<?php

namespace App\Console\Commands;

use App\Mail\TrialExpirando1DiaMail;
use App\Mail\TrialExpirando3DiasMail;
use App\Mail\TrialExpiradoMail;
use App\Models\SubscriptionLog;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckTrialExpiryCommand extends Command
{
    protected $signature   = 'pitstop:check-trial-expiry';
    protected $description = 'Envia emails de alerta de expiração de trial (D-3, D-1, D0)';

    public function handle(): int
    {
        $tenants = Tenant::whereNotNull('trial_ends_at')
            ->where('plano_ativo', false)
            ->get();

        foreach ($tenants as $tenant) {
            $dias = (int) now()->startOfDay()->diffInDays($tenant->trial_ends_at->startOfDay(), false);

            if ($dias === 3 && ! $this->jaEnviou($tenant->id, 'email_trial_expirando_3_dias')) {
                $this->enviarEmail($tenant, 'email_trial_expirando_3_dias', new TrialExpirando3DiasMail($tenant));
            }

            if ($dias === 1 && ! $this->jaEnviou($tenant->id, 'email_trial_expirando_1_dia')) {
                $this->enviarEmail($tenant, 'email_trial_expirando_1_dia', new TrialExpirando1DiaMail($tenant));
            }

            if ($dias === 0 && ! $this->jaEnviou($tenant->id, 'email_trial_expirado')) {
                $this->enviarEmail($tenant, 'email_trial_expirado', new TrialExpiradoMail($tenant));
            }
        }

        $this->info('Verificação de trial concluída.');
        return Command::SUCCESS;
    }

    private function jaEnviou(int $tenantId, string $evento): bool
    {
        return SubscriptionLog::where('tenant_id', $tenantId)
            ->where('evento', $evento)
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
                'payload_json' => json_encode(['sent_at' => now()->toIso8601String(), 'email' => $adminEmail]),
            ]);
        } catch (\Throwable $e) {
            Log::error("CheckTrialExpiry: erro ao enviar {$evento} para tenant {$tenant->id}: {$e->getMessage()}");
        }
    }
}
