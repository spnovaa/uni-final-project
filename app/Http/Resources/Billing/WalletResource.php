<?php

namespace App\Http\Resources\Billing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="WalletResource",
 *     type="object",
 *     title="Wallet",
 *     description="Wallet balance",
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="balance", type="number", format="float", example=42.50),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-29T08:00:00Z")
 * )
 */
class WalletResource extends JsonResource
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
            'balance' => (float) $this->balance,
            'currency' => $this->currency,
            'updated_at' => $this->updated_at,
        ];
    }
}
