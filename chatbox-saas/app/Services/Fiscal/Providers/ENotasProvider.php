<?php

namespace App\Services\Fiscal\Providers;

use App\Contracts\Fiscal\IFiscalProvider;
use App\Models\Invoice;
use App\Models\PaymentHistory;
use Illuminate\Support\Facades\Log;

class ENotasProvider implements IFiscalProvider
{
    public function emitInvoice(PaymentHistory $payment, array $customerData): Invoice
    {
        Log::info("ENotasProvider: Emitting invoice for payment {$payment->id}");
        
        // TODO: Implement ENotas API call
        
        return new Invoice();
    }

    public function cancelInvoice(Invoice $invoice, string $reason): bool
    {
        Log::info("ENotasProvider: Canceling invoice {$invoice->id}");
        return true;
    }

    public function syncInvoice(Invoice $invoice): void
    {
        Log::info("ENotasProvider: Syncing invoice {$invoice->id}");
    }
}
