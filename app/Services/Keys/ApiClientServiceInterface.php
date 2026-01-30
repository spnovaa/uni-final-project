<?php

namespace App\Services\Keys;

use App\Models\ApiClient;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * API client service contract.
 *
 * API clients are user-owned containers for issuing and managing multiple API keys.
 */
interface ApiClientServiceInterface
{
    /**
     * List API clients owned by a user.
     * @param User $user
     * @return Collection
     */
    public function list(User $user): Collection;

    /**
     * Create a new API client for a user.
     * @param User $user
     * @param string $name
     * @return ApiClient
     */
    public function create(User $user, string $name): ApiClient;
}
