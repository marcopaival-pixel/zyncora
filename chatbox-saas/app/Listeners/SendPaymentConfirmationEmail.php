<?php

namespace App\Listeners;

use App\Events\PaymentApproved;
use App\Mail\PaymentConfirmedMail;
use Illuminate\Support\Facades\Mail;

class SendPaymentConfirmationEmail
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
        $company = $event->paymentHistory->company;
        $users = $company->users()->where('is_super_admin', true)->orWhere('role', 'admin')->get();

        foreach ($users as $user) {
            Mail::to($user->email)->queue(new PaymentConfirmedMail($event->paymentHistory));
        }
    }
}
