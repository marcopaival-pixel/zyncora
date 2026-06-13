<?php

namespace App\Jobs;

use App\Contracts\Fiscal\IFiscalProvider;
use App\Models\PaymentHistory;
use Illuminate\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EmitInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payment;

    /**
     * Create a new job instance.
     */
    public function __construct(PaymentHistory $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     */
    public function handle(IFiscalProvider $fiscalProvider): void
    {
        try {
            $customerData = [
                'name' => $this->payment->company->name,
                'document' => $this->payment->company->document ?? '00000000000',
                // more customer data can be added
            ];

            $invoice = $fiscalProvider->emitInvoice($this->payment, $customerData);

            Log::info("EmitInvoiceJob: Invoice successfully requested for payment {$this->payment->id}.");
        } catch (\Exception $e) {
            Log::error('EmitInvoiceJob Failed: '.$e->getMessage());
        }
    }
}
