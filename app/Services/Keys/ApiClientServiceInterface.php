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
     * List.
     * @param User $user
     * @return Collection
     */
    public function list(User $user): Collection;

    /**
     * Create.
     * @param User $user
     * @param string $name
     * @return ApiClient
     */
    public function create(User $user, string $name): ApiClient;
}
