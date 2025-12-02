<?php

namespace App\Mail;

use App\Models\Affiliate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AffiliateApproved extends Mailable
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
            subject: 'Félicitations ! Votre demande de partenariat est approuvée',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.affiliate-approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
