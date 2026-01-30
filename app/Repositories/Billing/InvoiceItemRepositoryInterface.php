<?php

namespace App\Repositories\Billing;

use App\Models\Invoice;
use Illuminate\Support\Collection;

/**
 * Persistence layer for invoice item.
 */
interface InvoiceItemRepositoryInterface
{
    /**
     * Create many.
     * @param Invoice $invoice
     * @param array $items
     * @return Collection
     */
    public function createMany(Invoice $invoice, array $items): Collection;
}
