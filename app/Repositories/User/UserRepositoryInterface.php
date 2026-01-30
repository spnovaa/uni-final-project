<?php

namespace App\Repositories\User;

use App\Models\User;

/**
 * Repository contract for querying and persisting User records.
 */
interface UserRepositoryInterface
{
    /**
     * Find a user by ID.
     * @param int $id
     * @return ?User
     */
    public function find(int $id): ?User;

    /**
     * Find a user by email address.
     * @param string $email
     * @return ?User
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find a user by phone number.
     * @param string $phone
     * @return ?User
     */
    public function findByPhone(string $phone): ?User;

    /**
     * Create a new user record.
     * @param array $data
     * @return User
     */
    public function create(array $data): User;

    /**
     * Persist changes to an existing user model.
     * @param User $user
     * @return User
     */
    public function save(User $user): User;
}
