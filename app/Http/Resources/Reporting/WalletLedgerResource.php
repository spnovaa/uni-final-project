<?php

namespace App\Http\Resources\Reporting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="WalletLedgerResource",
 *     type="object",
 *     title="Wallet Ledger Entry",
 *     description="Wallet ledger entry",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="type", type="string", example="debit"),
 *     @OA\Property(property="amount", type="number", format="float", example=2.5),
 *     @OA\Property(property="reason", type="string", example="usage"),
 *     @OA\Property(property="ref_type", type="string", nullable=true),
 *     @OA\Property(property="ref_id", type="string", nullable=true),
 *     @OA\Property(property="meta", type="object", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
class WalletLedgerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'reason' => $this->reason,
            'ref_type' => $this->ref_type,
            'ref_id' => $this->ref_id,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
        ];
    }
}
