<?php

namespace App\Services\User;

use App\Models\User;

interface UserServiceInterface
{
    public function updateProfile(User $user, array $data): User;
}
