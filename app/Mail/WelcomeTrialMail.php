<?php

namespace App\Mail;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeTrialMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sua conta no PitStop está ativa! Boas-vindas 🚀',
        );
    }

    public function content(): Content
    {
        $appUrl  = rtrim(config('pitstop.signup.app_url', 'https://app.iaqueatende.com.br'), '/');
        $trialFim = $this->tenant->trial_ends_at;

        return new Content(
            view: 'emails.welcome-trial',
            with: [
                'nomeUsuario'  => $this->user->name,
                'nomeOficina'  => $this->tenant->nome,
                'login'        => $this->user->username,
                'loginUrl'     => $appUrl . '/login',
                'trialFim'     => $trialFim,
                'diasTrial'    => (int) config('pitstop.signup.trial_dias', 30),
            ],
        );
    }
}
