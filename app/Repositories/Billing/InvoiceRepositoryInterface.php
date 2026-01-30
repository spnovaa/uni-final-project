<?php

namespace App\Repositories\Billing;

use App\Models\Invoice;
use Illuminate\Support\Collection;

/**
 * Repository contract for querying and persisting Invoice records.
 */
interface InvoiceRepositoryInterface
{
    /**
     * List invoices owned by a user.
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function listByUser(int $userId, int $limit = 50): Collection;

    /**
     * Find an invoice by ID, scoping to a user.
     * @param int $userId
     * @param int $invoiceId
     * @return ?Invoice
     */
    public function findForUser(int $userId, int $invoiceId): ?Invoice;

    /**
     * Create a new invoice record.
     * @param array $data
     * @return Invoice
     */
    public function create(array $data): Invoice;

    /**
     * Persist changes to an invoice model.
     * @param Invoice $invoice
     * @return Invoice
     */
    public function save(Invoice $invoice): Invoice;
}
