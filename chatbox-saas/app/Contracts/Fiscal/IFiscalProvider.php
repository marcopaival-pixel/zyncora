<?php

namespace App\Contracts\Fiscal;

use App\Models\Invoice;
use App\Models\PaymentHistory;

interface IFiscalProvider
{
    /**
     * Emits a new invoice based on the payment history.
     */
    public function emitInvoice(PaymentHistory $payment, array $customerData): Invoice;

    /**
     * Cancels an existing invoice.
     */
    public function cancelInvoice(Invoice $invoice, string $reason): bool;

    /**
     * Syncs the invoice status and PDF URL from the provider.
     */
    public function syncInvoice(Invoice $invoice): void;
}
