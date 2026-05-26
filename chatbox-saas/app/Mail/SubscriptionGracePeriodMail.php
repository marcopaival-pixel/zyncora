<?php

namespace App\Mail;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionGracePeriodMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Company $company,
        public int $daysRemaining,
        public ?Carbon $graceEndsAt = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Urgente: assinatura Chatbox em período de graça',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.subscription-grace-period',
            with: [
                'companyName' => $this->company->name,
                'daysRemaining' => $this->daysRemaining,
                'graceEndsAt' => $this->graceEndsAt,
                'billingUrl' => url('/admin/billing'),
            ],
        );
    }
}
