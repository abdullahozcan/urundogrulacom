<?php

namespace App\Mail;

use App\Helpers\SettingHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $data
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: SettingHelper::get('contact_form_to', 'info@urundogrula.com'),
            subject: 'İletişim Formu - ' . str_replace(["\r", "\n"], '', $this->data['name'] ?? ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact',
        );
    }
}
