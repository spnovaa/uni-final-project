<?php

namespace App\Http\Resources\Gateway;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ProviderModelResource",
 *     type="object",
 *     title="Provider Model",
 *     description="Provider model mapping",
 *     @OA\Property(property="id", type="integer", example=5),
 *     @OA\Property(property="provider_id", type="integer", example=1),
 *     @OA\Property(property="model_key", type="string", example="gpt-4o-mini"),
 *     @OA\Property(property="capabilities", type="object", example={"chat":true}),
 *     @OA\Property(property="pricing_config", type="object", example={"input_token":0.0005,"output_token":0.001}),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-29T08:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-29T08:00:00Z")
 * )
 */
class ProviderModelResource extends JsonResource
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
            'provider_id' => $this->provider_id,
            'model_key' => $this->model_key,
            'capabilities' => $this->capabilities,
            'pricing_config' => $this->pricing_config,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
