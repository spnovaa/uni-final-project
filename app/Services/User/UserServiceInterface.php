<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Http\UploadedFile;

/**
 * User profile service contract.
 *
 * Implementations may apply caching and storage concerns while keeping controller logic small.
 */
interface UserServiceInterface
{
    /**
     * Get the latest user profile information (may be cached).
     * @param User $user
     * @return User
     */
    public function getProfile(User $user): User;

    /**
     * Update user profile fields and optionally replace the profile image.
     * @param User $user
     * @param array $data
     * @param ?UploadedFile $profileImage
     * @return User
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $profileImage = null): User;
}
