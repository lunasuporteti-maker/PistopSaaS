<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlanoExpirando7DiasMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Tenant $tenant) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Seu plano PitStop vence em 7 dias');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.plano-expirando-7-dias');
    }
}
