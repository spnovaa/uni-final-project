<?php

namespace App\Http\Resources\Reporting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UsageReportResource",
 *     type="object",
 *     title="Usage Report Row",
 *     description="Aggregated usage report row",
 *     @OA\Property(property="group_by", type="string", example="day"),
 *     @OA\Property(property="group_value", type="string", example="2026-01-29"),
 *     @OA\Property(property="metric", type="string", example="tokens_in"),
 *     @OA\Property(property="quantity", type="number", format="float", example=1200),
 *     @OA\Property(property="total_cost", type="number", format="float", example=0.6)
 * )
 */
class UsageReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'group_by' => $this['group_by'] ?? null,
            'group_value' => $this['group_value'] ?? null,
            'metric' => $this['metric'] ?? null,
            'quantity' => $this['quantity'] ?? 0,
            'total_cost' => $this['total_cost'] ?? 0,
        ];
    }
}
