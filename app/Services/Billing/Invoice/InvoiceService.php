<?php

namespace App\Services\Billing\Invoice;

use App\Models\Invoice;
use App\Models\User;
use App\Repositories\Billing\InvoiceItemRepositoryInterface;
use App\Repositories\Billing\InvoiceRepositoryInterface;
use Dompdf\Dompdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InvoiceService implements InvoiceServiceInterface
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoices,
        private readonly InvoiceItemRepositoryInterface $items
    ) {
    }

    public function list(User $user, int $limit = 50): Collection
    {
        return $this->invoices->listByUser($user->id, $limit);
    }

    public function get(User $user, int $invoiceId): Invoice
    {
        $invoice = $this->invoices->findForUser($user->id, $invoiceId);

        if (! $invoice) {
            throw ValidationException::withMessages([
                'invoice' => ['Invoice not found.'],
            ]);
        }

        return $invoice;
    }

    public function createDraft(User $user, array $items, ?string $currency = null, float $tax = 0): Invoice
    {
        $preparedItems = $this->prepareItems($items);
        $subtotal = $this->sumSubtotal($preparedItems);
        $total = $subtotal + $tax;

        return DB::transaction(function () use ($user, $currency, $tax, $subtotal, $total, $preparedItems) {
            $invoice = $this->invoices->create([
                'user_id' => $user->id,
                'number' => $this->generateNumber(),
                'status' => 'draft',
                'currency' => $currency ?: 'USD',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            $this->items->createMany($invoice, $preparedItems);

            return $invoice->refresh()->load('items');
        });
    }

    public function issue(Invoice $invoice): Invoice
    {
        $invoice->status = 'issued';
        $invoice->issued_at = now();

        return $this->invoices->save($invoice);
    }

    public function markPaid(Invoice $invoice): Invoice
    {
        $invoice->status = 'paid';
        $invoice->paid_at = now();

        return $this->invoices->save($invoice);
    }

    public function generatePdf(Invoice $invoice): string
    {
        $invoice->loadMissing(['items', 'user']);

        $html = view('invoices.pdf', [
            'invoice' => $invoice,
        ])->render();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();

        $path = $this->pdfPath($invoice);
        Storage::disk('local')->put($path, $dompdf->output());

        return $path;
    }

    public function pdfPath(Invoice $invoice): string
    {
        return 'invoices/invoice_'.$invoice->id.'.pdf';
    }

    private function prepareItems(array $items): array
    {
        return collect($items)->map(function ($item) {
            $quantity = (float) ($item['quantity'] ?? 1);
            $unitPrice = (float) ($item['unit_price'] ?? 0);

            return [
                'type' => $item['type'] ?? 'usage',
                'description' => $item['description'] ?? 'Usage',
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $quantity * $unitPrice,
                'meta' => $item['meta'] ?? null,
            ];
        })->all();
    }

    private function sumSubtotal(array $items): float
    {
        return (float) collect($items)->sum('line_total');
    }

    private function generateNumber(): string
    {
        do {
            $number = sprintf('INV-%s-%04d', now()->format('Ymd'), random_int(1, 9999));
        } while (Invoice::query()->where('number', $number)->exists());

        return $number;
    }
}
