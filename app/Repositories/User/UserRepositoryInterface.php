<?php

namespace App\Repositories\User;

use App\Models\User;

/**
 * Persistence layer for user.
 */
interface UserRepositoryInterface
{
    /**
     * Find.
     * @param int $id
     * @return ?User
     */
    public function find(int $id): ?User;

    /**
     * Find by email.
     * @param string $email
     * @return ?User
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find by phone.
     * @param string $phone
     * @return ?User
     */
    public function findByPhone(string $phone): ?User;

    /**
     * Create User.
     * @param array $data
     * @return User
     */
    public function create(array $data): User;

    /**
     * Save.
     * @param User $user
     * @return User
     */
    public function save(User $user): User;
}
