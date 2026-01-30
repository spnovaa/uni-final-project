<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\Cache\CacheServiceInterface;

/**
 * User profile service with cache-aside and profile image storage.
 *
 * Responsibilities:
 * - Read the authenticated user's profile (optionally from cache).
 * - Update profile fields and profile image, invalidating cached profile data on write.
 */
class UserService implements UserServiceInterface
{
    /**
     * Create a new instance.
     * @param UserRepositoryInterface $users
     * @param CacheServiceInterface $cache
     * @return void
     */
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly CacheServiceInterface $cache,
    )
    {
    }

    /**
     * Get the latest user profile information.
     *
     * Uses cache-aside: reads from cache first and falls back to the repository when missing.
     * @param User $user
     * @return User
     */
    public function getProfile(User $user): User
    {
        $ttl = $this->cache->ttl('profile', (int) config('cache.profile_ttl', 300));

        return $this->cache->remember(
            $this->cacheKey($user->id),
            $ttl,
            fn () => $this->users->find($user->id) ?? $user
        );
    }

    /**
     * Update user profile fields and optionally replace the profile image.
     *
     * After persisting changes, invalidates the cached profile entry so the next read is fresh.
     * @param User $user
     * @param array $data
     * @param ?UploadedFile $profileImage
     * @return User
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $profileImage = null): User
    {
        $user->fill($data);

        if ($profileImage) {
            $path = $this->storeProfileImage($user, $profileImage);
            $user->profile_image_path = $path;
        }

        $user = $this->users->save($user);

        $this->cache->forget($this->cacheKey($user->id));

        return $user;
    }

    /**
     * Build the cache key used for storing a user's profile.
     * @param int $userId
     * @return string
     */
    private function cacheKey(int $userId): string
    {
        return $this->cache->key('user', 'profile', (string) $userId);
    }

    /**
     * Store a new profile image and delete the previous one if present.
     *
     * Files are stored on the `public` disk under a per-user folder.
     * @param User $user
     * @param UploadedFile $profileImage
     * @return string
     */
    private function storeProfileImage(User $user, UploadedFile $profileImage): string
    {
        if ($user->profile_image_path) {
            Storage::disk('public')->delete($user->profile_image_path);
        }

        $fileName = Str::uuid()->toString().'.'.$profileImage->getClientOriginalExtension();

        return $profileImage->storeAs(
            'profile_images/'.$user->id,
            $fileName,
            'public'
        );
    }
}
