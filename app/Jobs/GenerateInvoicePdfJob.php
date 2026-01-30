<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\Billing\Invoice\InvoiceServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Generate and persist a PDF representation of an invoice.
 *
 * The HTTP endpoint can enqueue this job when an invoice PDF is requested but not yet generated.
 */
class GenerateInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new instance.
     * @param int $invoiceId
     * @return void
     */
    public function __construct(public int $invoiceId)
    {
    }

    /**
     * Load the invoice and delegate PDF generation to the invoice service.
     * @param InvoiceServiceInterface $invoices
     * @return void
     */
    public function handle(InvoiceServiceInterface $invoices): void
    {
        $invoice = Invoice::query()->find($this->invoiceId);

        if (! $invoice) {
            return;
        }

        $invoices->generatePdf($invoice);
    }
}
