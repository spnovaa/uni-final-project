<?php

namespace App\Repositories\Billing;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Collection;

/**
 * Persistence layer for invoice item.
 */
class InvoiceItemRepository implements InvoiceItemRepositoryInterface
{
    /**
     * Create many.
     * @param Invoice $invoice
     * @param array $items
     * @return Collection
     */
    public function createMany(Invoice $invoice, array $items): Collection
    {
        $created = collect();

        foreach ($items as $item) {
            $created->push(InvoiceItem::query()->create([
                'invoice_id' => $invoice->id,
                'type' => $item['type'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $item['line_total'],
                'meta' => $item['meta'] ?? null,
            ]));
        }

        return $created;
    }
}
