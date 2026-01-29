<?php

namespace App\Repositories\Billing;

use App\Models\Invoice;
use Illuminate\Support\Collection;

interface InvoiceRepositoryInterface
{
    public function listByUser(int $userId, int $limit = 50): Collection;

    public function findForUser(int $userId, int $invoiceId): ?Invoice;

    public function create(array $data): Invoice;

    public function save(Invoice $invoice): Invoice;
}
