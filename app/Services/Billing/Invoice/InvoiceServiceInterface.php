<?php

namespace App\Services\Billing\Invoice;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Service layer for invoice.
 */
interface InvoiceServiceInterface
{
    /**
     * List.
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function list(User $user, int $limit = 50): Collection;

    /**
     * Get.
     * @param User $user
     * @param int $invoiceId
     * @return Invoice
     */
    public function get(User $user, int $invoiceId): Invoice;

    /**
     * Create draft.
     * @param User $user
     * @param array $items
     * @param ?string $currency
     * @param float $tax
     * @return Invoice
     */
    public function createDraft(User $user, array $items, ?string $currency = null, float $tax = 0): Invoice;

    /**
     * Issue.
     * @param Invoice $invoice
     * @return Invoice
     */
    public function issue(Invoice $invoice): Invoice;

    /**
     * Mark paid.
     * @param Invoice $invoice
     * @return Invoice
     */
    public function markPaid(Invoice $invoice): Invoice;

    /**
     * Generate pdf.
     * @param Invoice $invoice
     * @return string
     */
    public function generatePdf(Invoice $invoice): string;

    /**
     * Pdf path.
     * @param Invoice $invoice
     * @return string
     */
    public function pdfPath(Invoice $invoice): string;
}
