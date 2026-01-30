<?php

namespace App\Repositories\Keys;

use App\Models\ApiClient;
use Illuminate\Support\Collection;

/**
 * Persistence layer for api client.
 */
interface ApiClientRepositoryInterface
{
    /**
     * List by user.
     * @param int $userId
     * @return Collection
     */
    public function listByUser(int $userId): Collection;

    /**
     * Create API client.
     * @param array $data
     * @return ApiClient
     */
    public function create(array $data): ApiClient;

    /**
     * Find.
     * @param int $id
     * @return ?ApiClient
     */
    public function find(int $id): ?ApiClient;
}
