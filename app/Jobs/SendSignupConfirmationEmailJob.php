<?php

namespace App\Jobs;

use App\Mail\SignupConfirmationMail;
use App\Models\TenantSignup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Despacha o e-mail de confirmação de cadastro via fila (PRD 03, AC8).
 *
 * Queue driver: database. Reenviado pela action resendEmail do controller.
 */
class SendSignupConfirmationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public TenantSignup $signup) {}

    public function handle(): void
    {
        Mail::to($this->signup->email)
            ->send(new SignupConfirmationMail($this->signup));
    }
}
