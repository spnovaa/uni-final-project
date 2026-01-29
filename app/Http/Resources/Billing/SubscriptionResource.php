<?php

namespace App\Http\Resources\Billing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SubscriptionResource",
 *     type="object",
 *     title="Subscription",
 *     description="Subscription resource",
 *     @OA\Property(property="id", type="integer", example=25),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(property="starts_at", type="string", format="date-time", example="2026-01-29T08:00:00Z"),
 *     @OA\Property(property="ends_at", type="string", format="date-time", example="2026-02-29T08:00:00Z"),
 *     @OA\Property(property="renewal_at", type="string", format="date-time", example="2026-02-29T08:00:00Z"),
 *     @OA\Property(property="canceled_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="plan", ref="#/components/schemas/PlanResource")
 * )
 */
class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'renewal_at' => $this->renewal_at,
            'canceled_at' => $this->canceled_at,
            'plan' => new PlanResource($this->whenLoaded('plan')),
        ];
    }
}
