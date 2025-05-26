<?php

namespace App\Mail;

use App\Behaviors\EmailBehaviors;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConviteMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Dados do convite.
     */
    public $link;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        $this->link = config('services.front_end.url');
    }

    /*
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Convite Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {

        return new Content(
            view: 'emails.convite',
            with: [
                'link' => $this->link . '/cadastro',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
