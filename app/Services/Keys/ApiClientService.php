<?php

namespace App\Services\Keys;

use App\Models\ApiClient;
use App\Models\User;
use App\Repositories\Keys\ApiClientRepositoryInterface;
use Illuminate\Support\Collection;

class ApiClientService implements ApiClientServiceInterface
{
    public function __construct(private readonly ApiClientRepositoryInterface $clients)
    {
    }

    public function list(User $user): Collection
    {
        return $this->clients->listByUser($user->id);
    }

    public function create(User $user, string $name): ApiClient
    {
        return $this->clients->create([
            'user_id' => $user->id,
            'name' => $name,
            'status' => 'active',
        ]);
    }
}
