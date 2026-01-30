<?php

namespace App\Repositories\Billing;

use App\Models\Invoice;
use Illuminate\Support\Collection;

/**
 * Repository contract for creating InvoiceItem records.
 */
interface InvoiceItemRepositoryInterface
{
    /**
     * Create multiple invoice items for an invoice.
     * @param Invoice $invoice
     * @param array $items
     * @return Collection
     */
    public function createMany(Invoice $invoice, array $items): Collection;
}
