<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Http\UploadedFile;

interface UserServiceInterface
{
    public function getProfile(User $user): User;

    public function updateProfile(User $user, array $data, ?UploadedFile $profileImage = null): User;
}
