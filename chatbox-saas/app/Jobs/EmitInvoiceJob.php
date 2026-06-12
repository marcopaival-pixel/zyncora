<?php

namespace App\Jobs;

use Illuminate\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EmitInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payment;

    /**
     * Create a new job instance.
     */
    public function __construct(\App\Models\PaymentHistory $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Contracts\Fiscal\IFiscalProvider $fiscalProvider): void
    {
        try {
            $customerData = [
                'name' => $this->payment->company->name,
                'document' => $this->payment->company->document ?? '00000000000',
                // more customer data can be added
            ];

            $invoice = $fiscalProvider->emitInvoice($this->payment, $customerData);
            
            \Illuminate\Support\Facades\Log::info("EmitInvoiceJob: Invoice successfully requested for payment {$this->payment->id}.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("EmitInvoiceJob Failed: " . $e->getMessage());
        }
    }
}
