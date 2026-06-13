<?php

namespace App\Listeners;

use App\Events\PaymentApproved;
use App\Jobs\EmitInvoiceJob;
use Illuminate\Support\Facades\Log;

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
            Log::info("ValidatePaymentListener: Payment {$payment->id} is valid. Dispatching EmitInvoiceJob.");
            EmitInvoiceJob::dispatch($payment);
        } else {
            Log::warning("ValidatePaymentListener: Payment {$payment->id} validation failed.");
        }
    }
}
