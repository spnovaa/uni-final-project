<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\Billing\Invoice\InvoiceServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $invoiceId)
    {
    }

    public function handle(InvoiceServiceInterface $invoices): void
    {
        $invoice = Invoice::query()->find($this->invoiceId);

        if (! $invoice) {
            return;
        }

        $invoices->generatePdf($invoice);
    }
}
