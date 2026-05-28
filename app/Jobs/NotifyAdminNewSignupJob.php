<?php

namespace App\Jobs;

use App\Models\TenantSignup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyAdminNewSignupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $signupId) {}

    public function handle(): void
    {
        $signup = TenantSignup::find($this->signupId);
        if (! $signup) {
            return;
        }

        $adminUrl = url("/admin/tenants");
        if ($signup->tenant_id) {
            $adminUrl = url("/admin/tenants/{$signup->tenant_id}");
        }

        $payload = [
            'nome_oficina' => $signup->nome_oficina,
            'email'        => $signup->email,
            'cidade'       => $signup->cidade,
            'uf'           => $signup->uf,
            'plano'        => $signup->plano_escolhido,
            'admin_url'    => $adminUrl,
            'timestamp'    => now()->toIso8601String(),
        ];

        // Envio por email
        $adminEmail = config('pitstop.admin_notify_email');
        if ($adminEmail) {
            try {
                Mail::raw(
                    "Novo cadastro no PitStop!\n\n"
                    . "Oficina: {$payload['nome_oficina']}\n"
                    . "Email: {$payload['email']}\n"
                    . "Cidade: {$payload['cidade']}/{$payload['uf']}\n"
                    . "Plano: {$payload['plano']}\n\n"
                    . "Ver no admin: {$payload['admin_url']}",
                    fn ($m) => $m->to($adminEmail)->subject("Novo cadastro: {$signup->nome_oficina}")
                );
            } catch (\Throwable $e) {
                Log::error('[NotifyAdmin] Erro ao enviar email: ' . $e->getMessage());
            }
        }

        // Webhook opcional
        $webhookUrl = config('pitstop.admin_notify_webhook_url');
        if ($webhookUrl) {
            try {
                Http::post($webhookUrl, $payload);
            } catch (\Throwable $e) {
                Log::error('[NotifyAdmin] Erro ao enviar webhook: ' . $e->getMessage());
            }
        }
    }
}
