<?php

namespace App\Services\Keys;

use App\Models\ApiClient;
use App\Models\User;
use App\Repositories\Keys\ApiClientRepositoryInterface;
use App\Services\Audit\AuditLogServiceInterface;
use Illuminate\Support\Collection;

/**
 * Manage API clients (logical groupings for API keys).
 *
 * API clients belong to a user and are used to issue/revoke multiple API keys while keeping
 * reporting and access management organized.
 */
class ApiClientService implements ApiClientServiceInterface
{
    /**
     * Create a new instance.
     * @param ApiClientRepositoryInterface $clients
     * @param AuditLogServiceInterface $audit
     * @return void
     */
    public function __construct(
        private readonly ApiClientRepositoryInterface $clients,
        private readonly AuditLogServiceInterface $audit
    ) {
    }

    /**
     * List API clients owned by a user.
     * @param User $user
     * @return Collection
     */
    public function list(User $user): Collection
    {
        return $this->clients->listByUser($user->id);
    }

    /**
     * Create a new API client and record an audit entry.
     * @param User $user
     * @param string $name
     * @return ApiClient
     */
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
