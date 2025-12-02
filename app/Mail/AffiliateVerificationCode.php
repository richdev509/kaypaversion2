<?php

namespace App\Mail;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliateVerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    public $affiliate;
    public $code;

    public function __construct(Affiliate $affiliate, string $code)
    {
        $this->affiliate = $affiliate;
        $this->code = $code;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Code de vérification - Devenir Partenaire Kaypa',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.affiliate-verification-code',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
