<?php

namespace App\Services\Billing\Invoice;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Collection;

interface InvoiceServiceInterface
{
    public function list(User $user, int $limit = 50): Collection;

    public function get(User $user, int $invoiceId): Invoice;

    public function createDraft(User $user, array $items, ?string $currency = null, float $tax = 0): Invoice;

    public function issue(Invoice $invoice): Invoice;

    public function markPaid(Invoice $invoice): Invoice;

    public function generatePdf(Invoice $invoice): string;

    public function pdfPath(Invoice $invoice): string;
}
