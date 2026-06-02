<?php

namespace App\Jobs;

use App\Mail\WelcomeTrialMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeTrialEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public int $tenantId,
        public int $userId,
    ) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        $user   = User::find($this->userId);

        if (! $tenant || ! $user || ! $user->email) {
            Log::warning('[WelcomeTrialEmail] Tenant ou user não encontrado', [
                'tenant_id' => $this->tenantId,
                'user_id'   => $this->userId,
            ]);

            return;
        }

        Mail::to($user->email)->send(new WelcomeTrialMail($tenant, $user));

        Log::info('[WelcomeTrialEmail] Enviado', ['tenant' => $tenant->nome, 'email' => $user->email]);
    }
}
