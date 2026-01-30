<?php

namespace App\Http\Resources\Billing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="WalletTransactionResource",
 *     type="object",
 *     title="Wallet Transaction",
 *     description="Wallet transaction entry",
 *     @OA\Property(property="id", type="integer", example=100),
 *     @OA\Property(property="type", type="string", example="credit"),
 *     @OA\Property(property="amount", type="number", format="float", example=20.00),
 *     @OA\Property(property="reason", type="string", example="topup"),
 *     @OA\Property(property="ref_type", type="string", example="subscription"),
 *     @OA\Property(property="ref_id", type="integer", example=5),
 *     @OA\Property(property="meta", type="object", example={"plan_id":5}),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-29T08:00:00Z")
 * )
 */
class WalletTransactionResource extends JsonResource
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
            'amount' => (float) $this->amount,
            'reason' => $this->reason,
            'ref_type' => $this->ref_type,
            'ref_id' => $this->ref_id,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
        ];
    }
}
