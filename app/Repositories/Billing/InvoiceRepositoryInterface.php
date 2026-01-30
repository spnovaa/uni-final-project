<?php

namespace App\Repositories\Billing;

use App\Models\Invoice;
use Illuminate\Support\Collection;

/**
 * Persistence layer for invoice.
 */
interface InvoiceRepositoryInterface
{
    /**
     * List by user.
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function listByUser(int $userId, int $limit = 50): Collection;

    /**
     * Find for user.
     * @param int $userId
     * @param int $invoiceId
     * @return ?Invoice
     */
    public function findForUser(int $userId, int $invoiceId): ?Invoice;

    /**
     * Create.
     * @param array $data
     * @return Invoice
     */
    public function create(array $data): Invoice;

    /**
     * Save.
     * @param Invoice $invoice
     * @return Invoice
     */
    public function save(Invoice $invoice): Invoice;
}
