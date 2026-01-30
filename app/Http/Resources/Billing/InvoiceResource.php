<?php

namespace App\Http\Resources\Billing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

/**
 * @OA\Schema(
 *     schema="InvoiceResource",
 *     type="object",
 *     title="Invoice",
 *     description="Invoice summary",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="number", type="string", example="INV-20260129-0001"),
 *     @OA\Property(property="status", type="string", example="issued"),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="subtotal", type="number", format="float", example=10.00),
 *     @OA\Property(property="tax", type="number", format="float", example=0.00),
 *     @OA\Property(property="total", type="number", format="float", example=10.00),
 *     @OA\Property(property="issued_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/InvoiceItemResource")),
 *     @OA\Property(property="pdf_url", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'total' => $this->total,
            'issued_at' => $this->issued_at,
            'paid_at' => $this->paid_at,
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'pdf_url' => $this->id ? $this->signedPdfUrl() : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Signed pdf url.
     * @return string
     */
    private function signedPdfUrl(): string
    {
        return URL::temporarySignedRoute(
            'invoices.pdf',
            now()->addMinutes(15),
            ['invoice' => $this->id]
        );
    }
}
