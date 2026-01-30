<?php

namespace App\Repositories\Billing;

use App\Models\Invoice;
use Illuminate\Support\Collection;

/**
 * Persistence layer for invoice.
 */
class InvoiceRepository implements InvoiceRepositoryInterface
{
    /**
     * List by user.
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function listByUser(int $userId, int $limit = 50): Collection
    {
        return Invoice::query()
            ->with('items')
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Find for user.
     * @param int $userId
     * @param int $invoiceId
     * @return ?Invoice
     */
    public function findForUser(int $userId, int $invoiceId): ?Invoice
    {
        return Invoice::query()
            ->with('items')
            ->where('user_id', $userId)
            ->find($invoiceId);
    }

    /**
     * Create Invoice.
     * @param array $data
     * @return Invoice
     */
    public function create(array $data): Invoice
    {
        return Invoice::query()->create($data);
    }

    /**
     * Save.
     * @param Invoice $invoice
     * @return Invoice
     */
    public function save(Invoice $invoice): Invoice
    {
        $invoice->save();

        return $invoice;
    }
}
