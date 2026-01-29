<?php

namespace App\Http\Resources\Keys;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ApiKeyResource",
 *     type="object",
 *     title="API Key",
 *     description="API key resource",
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="key_prefix", type="string", example="abc12345"),
 *     @OA\Property(property="scopes", type="array", @OA\Items(type="string"), example={"ai:chat"}),
 *     @OA\Property(property="rate_limit_per_min", type="integer", example=60),
 *     @OA\Property(property="allowed_ips", type="array", @OA\Items(type="string"), example={"127.0.0.1"}),
 *     @OA\Property(property="expires_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="revoked_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
class ApiKeyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key_prefix' => $this->key_prefix,
            'scopes' => $this->scopes,
            'rate_limit_per_min' => $this->rate_limit_per_min,
            'allowed_ips' => $this->allowed_ips,
            'expires_at' => $this->expires_at,
            'revoked_at' => $this->revoked_at,
            'created_at' => $this->created_at,
        ];
    }
}
