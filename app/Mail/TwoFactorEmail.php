<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $url;

    public function __construct($token, $url = null)
    {
        $this->token = $token;
        $this->url = $url;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kétfaktoros hitelesítési kódod',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'auth.two-factor-email', // Fontos: markdown-ként kezeli
        );
    }

    public function attachments(): array
    {
        return [];
    }
}