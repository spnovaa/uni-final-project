<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Http\UploadedFile;

/**
 * Service layer for user.
 */
interface UserServiceInterface
{
    /**
     * Get profile.
     * @param User $user
     * @return User
     */
    public function getProfile(User $user): User;

    /**
     * Update profile.
     * @param User $user
     * @param array $data
     * @param ?UploadedFile $profileImage
     * @return User
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $profileImage = null): User;
}
