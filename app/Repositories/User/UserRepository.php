<?php

namespace App\Repositories\User;

use App\Models\User;

/**
 * Persistence layer for user.
 */
class UserRepository implements UserRepositoryInterface
{
    /**
     * Find.
     * @param int $id
     * @return ?User
     */
    public function find(int $id): ?User
    {
        return User::query()->find($id);
    }

    /**
     * Find by email.
     * @param string $email
     * @return ?User
     */
    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    /**
     * Find by phone.
     * @param string $phone
     * @return ?User
     */
    public function findByPhone(string $phone): ?User
    {
        return User::query()->where('phone', $phone)->first();
    }

    /**
     * Create User.
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    /**
     * Save.
     * @param User $user
     * @return User
     */
    public function save(User $user): User
    {
        $user->save();

        return $user;
    }
}
