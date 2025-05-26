<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class PublicMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public User $user, public Message $receivedMessage,)
    {

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $username = $this->user->name;
        return new Envelope(
            from: env('MAIL_FROM_ADDRESS', 'admin@vuejs-chat.com'),
            subject: "Message received from $username in public channel",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.public-message-received',
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
