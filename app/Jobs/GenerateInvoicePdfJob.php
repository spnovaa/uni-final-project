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
 * Queued job for generate invoice pdf.
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
     * Handle the queued job.
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
