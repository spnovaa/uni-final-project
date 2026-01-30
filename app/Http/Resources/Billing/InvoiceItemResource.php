<?php

namespace App\Http\Resources\Billing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="InvoiceItemResource",
 *     type="object",
 *     title="Invoice Item",
 *     description="Invoice line item",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="type", type="string", example="usage"),
 *     @OA\Property(property="description", type="string", example="Chat completions usage"),
 *     @OA\Property(property="quantity", type="number", format="float", example=1500),
 *     @OA\Property(property="unit_price", type="number", format="float", example=0.0005),
 *     @OA\Property(property="line_total", type="number", format="float", example=0.75),
 *     @OA\Property(property="meta", type="object", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
class InvoiceItemResource extends JsonResource
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
            'type' => $this->type,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'line_total' => $this->line_total,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
        ];
    }
}
