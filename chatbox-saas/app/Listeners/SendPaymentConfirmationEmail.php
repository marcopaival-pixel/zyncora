<?php

namespace App\Listeners;

use App\Events\PaymentApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
            \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\PaymentConfirmedMail($event->paymentHistory));
        }
    }
}
