<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginSuccessEmail extends Mailable
{
    use Queueable, SerializesModels;

    private null|string $ipAddress;
    private object $user;

    public function __construct($user, $ipAddress)
    {
        $this->ipAddress = $ipAddress;
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sekeres bejeltnezés',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'auth.login-success-email',
            with: [
                'user'  =>  $this->user,
                'now'   =>  now(),
                'ip'    =>  $this->ipAddress,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}