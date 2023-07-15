<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AddTeacherMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $link;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $id, $token)
    {
        $this->name = $name;
        $this->link = env('TEACHER_FRONTEND_URL').'/activate?id='.$id.'&token='.$token;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Teacher Registation on Schoolar',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.add_teacher_mail',
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
