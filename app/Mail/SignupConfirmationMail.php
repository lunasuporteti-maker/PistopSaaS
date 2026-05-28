<?php

namespace App\Mail;

use App\Models\TenantSignup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * E-mail de confirmação de cadastro (PRD 03, AC8).
 *
 * Contém o link de confirmação válido por 24h:
 * https://app.iaqueatende.com.br/confirmar-email/{token}
 */
class SignupConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TenantSignup $signup) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirme seu cadastro no PitStop',
        );
    }

    public function content(): Content
    {
        $appUrl = rtrim(config('pitstop.signup.app_url'), '/');
        $link = $appUrl.'/confirmar-email/'.$this->signup->token_confirmacao;

        return new Content(
            view: 'emails.signup-confirmation',
            with: [
                'nome' => $this->signup->nome_completo,
                'nomeOficina' => $this->signup->nome_oficina,
                'link' => $link,
                'expiraEm' => $this->signup->token_expira_em,
            ],
        );
    }
}
