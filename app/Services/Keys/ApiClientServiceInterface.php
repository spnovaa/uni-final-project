<?php

namespace App\Services\Keys;

use App\Models\ApiClient;
use App\Models\User;
use Illuminate\Support\Collection;

interface ApiClientServiceInterface
{
    public function list(User $user): Collection;

    public function create(User $user, string $name): ApiClient;
}
