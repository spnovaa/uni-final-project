<?php

namespace App\Repositories\Billing;

use App\Models\Invoice;
use Illuminate\Support\Collection;

interface InvoiceItemRepositoryInterface
{
    public function createMany(Invoice $invoice, array $items): Collection;
}
