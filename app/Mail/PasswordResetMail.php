<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $codigo;
    public $nombre;

    public function __construct(string $codigo, string $nombre)
    {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Restablece tu contraseña de EcoScan',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
            with: [
                'codigo' => $this->codigo,
                'nombre' => $this->nombre,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
