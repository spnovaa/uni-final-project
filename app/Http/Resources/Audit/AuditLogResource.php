<?php

namespace App\Http\Resources\Audit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="AuditLogResource",
 *     type="object",
 *     title="Audit Log",
 *     description="Audit log entry",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="actor_user_id", type="integer", nullable=true, example=10),
 *     @OA\Property(property="action", type="string", example="api_key.created"),
 *     @OA\Property(property="target_type", type="string", nullable=true, example="App\\Models\\ApiKey"),
 *     @OA\Property(property="target_id", type="string", nullable=true, example="15"),
 *     @OA\Property(property="meta", type="object", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
class AuditLogResource extends JsonResource
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
            'actor_user_id' => $this->actor_user_id,
            'action' => $this->action,
            'target_type' => $this->target_type,
            'target_id' => $this->target_id,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
        ];
    }
}
