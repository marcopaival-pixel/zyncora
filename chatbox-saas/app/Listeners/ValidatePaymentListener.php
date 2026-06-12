<?php

namespace App\Listeners;

use App\Events\PaymentApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ValidatePaymentListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentApproved $event): void
    {
        $payment = $event->payment;

        // Basic validation: amount must be > 0 and status must be paid
        if ($payment->amount > 0 && $payment->status === 'paid') {
            \Illuminate\Support\Facades\Log::info("ValidatePaymentListener: Payment {$payment->id} is valid. Dispatching EmitInvoiceJob.");
            \App\Jobs\EmitInvoiceJob::dispatch($payment);
        } else {
            \Illuminate\Support\Facades\Log::warning("ValidatePaymentListener: Payment {$payment->id} validation failed.");
        }
    }
}
