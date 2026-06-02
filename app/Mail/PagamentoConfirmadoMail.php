<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PagamentoConfirmadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public string $nomePlano,
        public float  $valor,
        public string $proximoVencimento,
        public string $emailDestinatario,
        public string $nomeDestinatario,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pagamento confirmado — PitStop ' . $this->nomePlano,
        );
    }

    public function content(): Content
    {
        $appUrl = rtrim(config('pitstop.signup.app_url', 'https://app.iaqueatende.com.br'), '/');

        return new Content(
            view: 'emails.pagamento-confirmado',
            with: [
                'nomeUsuario'       => $this->nomeDestinatario,
                'nomeOficina'       => $this->tenant->nome,
                'nomePlano'         => $this->nomePlano,
                'valor'             => $this->valor,
                'proximoVencimento' => $this->proximoVencimento,
                'loginUrl'          => $appUrl . '/login',
                'assinaturaUrl'     => $appUrl . '/assinatura',
            ],
        );
    }
}
