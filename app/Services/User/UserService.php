<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\Cache\CacheServiceInterface;

/**
 * Service layer for user.
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
     * Get profile.
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
     * Update profile.
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
     * Cache key.
     * @param int $userId
     * @return string
     */
    private function cacheKey(int $userId): string
    {
        return $this->cache->key('user', 'profile', (string) $userId);
    }

    /**
     * Store profile image.
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
