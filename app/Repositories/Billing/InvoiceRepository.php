<?php

namespace App\Repositories\Billing;

use App\Models\Invoice;
use Illuminate\Support\Collection;

class InvoiceRepository implements InvoiceRepositoryInterface
{
    public function listByUser(int $userId, int $limit = 50): Collection
    {
        return Invoice::query()
            ->with('items')
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function findForUser(int $userId, int $invoiceId): ?Invoice
    {
        return Invoice::query()
            ->with('items')
            ->where('user_id', $userId)
            ->find($invoiceId);
    }

    public function create(array $data): Invoice
    {
        return Invoice::query()->create($data);
    }

    public function save(Invoice $invoice): Invoice
    {
        $invoice->save();

        return $invoice;
    }
}
