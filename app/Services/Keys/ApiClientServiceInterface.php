<?php

namespace App\Services\Keys;

use App\Models\ApiClient;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Service layer for api client.
 */
interface ApiClientServiceInterface
{
    /**
     * List API clients.
     * @param User $user
     * @return Collection
     */
    public function list(User $user): Collection;

    /**
     * Create API client.
     * @param User $user
     * @param string $name
     * @return ApiClient
     */
    public function create(User $user, string $name): ApiClient;
}
