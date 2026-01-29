<?php

namespace App\Http\Resources\Gateway;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ProviderResource",
 *     type="object",
 *     title="Provider",
 *     description="AI provider",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="openai"),
 *     @OA\Property(property="type", type="string", example="openai_compatible"),
 *     @OA\Property(property="base_url", type="string", example="https://api.openai.com/v1"),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(property="priority", type="integer", example=0),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-29T08:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-29T08:00:00Z")
 * )
 */
class ProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'base_url' => $this->base_url,
            'status' => $this->status,
            'priority' => $this->priority,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
