<?php

namespace App\Repositories\User;

use App\Models\User;

/**
 * Repository for querying and persisting User records.
 */
class UserRepository implements UserRepositoryInterface
{
    /**
     * Find a user by ID.
     * @param int $id
     * @return ?User
     */
    public function find(int $id): ?User
    {
        return User::query()->find($id);
    }

    /**
     * Find a user by email address.
     * @param string $email
     * @return ?User
     */
    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    /**
     * Find a user by phone number.
     * @param string $phone
     * @return ?User
     */
    public function findByPhone(string $phone): ?User
    {
        return User::query()->where('phone', $phone)->first();
    }

    /**
     * Create a new user record.
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    /**
     * Persist changes to an existing user model.
     * @param User $user
     * @return User
     */
    public function save(User $user): User
    {
        $user->save();

        return $user;
    }
}
