<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;

class UserService implements UserServiceInterface
{
    public function __construct(private readonly UserRepositoryInterface $users)
    {
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->fill($data);

        return $this->users->save($user);
    }
}
