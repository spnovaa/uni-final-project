<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserResource",
 *     type="object",
 *     title="User",
 *     description="Authenticated user",
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="name", type="string", example="Jane Doe"),
 *     @OA\Property(property="email", type="string", example="jane@example.com"),
 *     @OA\Property(property="phone", type="string", example="+1234567890"),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(property="profile_image_url", type="string", nullable=true, example="https://example.com/storage/profile_images/user.png"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class UserResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'profile_image_url' => $this->profileImageUrl(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
