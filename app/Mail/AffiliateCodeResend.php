<?php

namespace App\Mail;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliateCodeResend extends Mailable
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
            subject: 'Votre code de parrainage Kaypa',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.affiliate-code-resend',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
