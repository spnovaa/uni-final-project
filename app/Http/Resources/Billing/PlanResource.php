<?php

namespace App\Http\Resources\Billing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="PlanResource",
 *     type="object",
 *     title="Subscription Plan",
 *     description="Plan resource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Starter"),
 *     @OA\Property(property="price", type="number", format="float", example=19.99),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="period", type="string", example="monthly"),
 *     @OA\Property(property="included_credits", type="number", format="float", example=1000),
 *     @OA\Property(property="rate_limits", type="object", example={"rpm":60}),
 *     @OA\Property(property="features", type="object", example={"priority_support":true}),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-29T08:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-29T08:00:00Z")
 * )
 */
class PlanResource extends JsonResource
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
            'name' => $this->name,
            'price' => (float) $this->price,
            'currency' => $this->currency,
            'period' => $this->period,
            'included_credits' => $this->included_credits !== null ? (float) $this->included_credits : null,
            'rate_limits' => $this->rate_limits,
            'features' => $this->features,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
