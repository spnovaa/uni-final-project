<?php

namespace App\Services\Billing\Invoice;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Invoice service contract.
 *
 * Invoices provide a billable summary and can optionally be rendered into PDF documents.
 */
interface InvoiceServiceInterface
{
    /**
     * List invoices for a user with a limit.
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function list(User $user, int $limit = 50): Collection;

    /**
     * Get an invoice owned by a user or throw when not found.
     * @param User $user
     * @param int $invoiceId
     * @return Invoice
     */
    public function get(User $user, int $invoiceId): Invoice;

    /**
     * Create a draft invoice with items and computed totals.
     * @param User $user
     * @param array $items
     * @param ?string $currency
     * @param float $tax
     * @return Invoice
     */
    public function createDraft(User $user, array $items, ?string $currency = null, float $tax = 0): Invoice;

    /**
     * Mark an invoice as issued.
     * @param Invoice $invoice
     * @return Invoice
     */
    public function issue(Invoice $invoice): Invoice;

    /**
     * Mark an invoice as paid.
     * @param Invoice $invoice
     * @return Invoice
     */
    public function markPaid(Invoice $invoice): Invoice;

    /**
     * Generate and store a PDF for an invoice and return its path.
     * @param Invoice $invoice
     * @return string
     */
    public function generatePdf(Invoice $invoice): string;

    /**
     * Compute the deterministic storage path for an invoice PDF.
     * @param Invoice $invoice
     * @return string
     */
    public function pdfPath(Invoice $invoice): string;
}
