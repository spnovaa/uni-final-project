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

/**
 * Service layer for invoice.
 */
class InvoiceService implements InvoiceServiceInterface
{
    /**
     * Create a new instance.
     * @param InvoiceRepositoryInterface $invoices
     * @param InvoiceItemRepositoryInterface $items
     * @return void
     */
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoices,
        private readonly InvoiceItemRepositoryInterface $items
    ) {
    }

    /**
     * List Invoices.
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function list(User $user, int $limit = 50): Collection
    {
        return $this->invoices->listByUser($user->id, $limit);
    }

    /**
     * Get.
     * @param User $user
     * @param int $invoiceId
     * @return Invoice
     */
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

    /**
     * Create draft.
     * @param User $user
     * @param array $items
     * @param ?string $currency
     * @param float $tax
     * @return Invoice
     */
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

    /**
     * Issue.
     * @param Invoice $invoice
     * @return Invoice
     */
    public function issue(Invoice $invoice): Invoice
    {
        $invoice->status = 'issued';
        $invoice->issued_at = now();

        return $this->invoices->save($invoice);
    }

    /**
     * Mark paid.
     * @param Invoice $invoice
     * @return Invoice
     */
    public function markPaid(Invoice $invoice): Invoice
    {
        $invoice->status = 'paid';
        $invoice->paid_at = now();

        return $this->invoices->save($invoice);
    }

    /**
     * Generate pdf.
     * @param Invoice $invoice
     * @return string
     */
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

    /**
     * Pdf path.
     * @param Invoice $invoice
     * @return string
     */
    public function pdfPath(Invoice $invoice): string
    {
        return 'invoices/invoice_'.$invoice->id.'.pdf';
    }

    /**
     * Prepare items.
     * @param array $items
     * @return array
     */
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

    /**
     * Sum subtotal.
     * @param array $items
     * @return float
     */
    private function sumSubtotal(array $items): float
    {
        return (float) collect($items)->sum('line_total');
    }

    /**
     * Generate number.
     * @return string
     */
    private function generateNumber(): string
    {
        do {
            $number = sprintf('INV-%s-%04d', now()->format('Ymd'), random_int(1, 9999));
        } while (Invoice::query()->where('number', $number)->exists());

        return $number;
    }
}
