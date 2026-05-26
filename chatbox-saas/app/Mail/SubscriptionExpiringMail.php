<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Company $company,
        public int $daysRemaining
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Aviso: assinatura Chatbox expira em breve',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.subscription-expiring',
            with: [
                'companyName' => $this->company->name,
                'daysRemaining' => $this->daysRemaining,
                'expiresAt' => $this->company->expires_at,
                'billingUrl' => url('/admin/billing'),
            ],
        );
    }
}
