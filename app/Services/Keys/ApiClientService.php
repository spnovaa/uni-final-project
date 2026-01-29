<?php

namespace App\Services\Keys;

use App\Models\ApiClient;
use App\Models\User;
use App\Repositories\Keys\ApiClientRepositoryInterface;
use App\Services\Audit\AuditLogServiceInterface;
use Illuminate\Support\Collection;

class ApiClientService implements ApiClientServiceInterface
{
    public function __construct(
        private readonly ApiClientRepositoryInterface $clients,
        private readonly AuditLogServiceInterface $audit
    ) {
    }

    public function list(User $user): Collection
    {
        return $this->clients->listByUser($user->id);
    }

    public function create(User $user, string $name): ApiClient
    {
        $client = $this->clients->create([
            'user_id' => $user->id,
            'name' => $name,
            'status' => 'active',
        ]);

        $this->audit->record($user, 'api_client.created', $client);

        return $client;
    }
}
