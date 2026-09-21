<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NotificationEquipe extends Mailable
{
    public function __construct(public string $sujet, public string $texte)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[PPN] '.$this->sujet);
    }

    public function content(): Content
    {
        return new Content(text: 'emails.notification');
    }
}
