<?php

namespace App\Mail;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliateRequestReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $affiliate;

    public function __construct(Affiliate $affiliate)
    {
        $this->affiliate = $affiliate;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Demande de partenariat reçue - Kaypa',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.affiliate-request-received',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
