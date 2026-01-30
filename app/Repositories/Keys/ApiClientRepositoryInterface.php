<?php

namespace App\Repositories\Keys;

use App\Models\ApiClient;
use Illuminate\Support\Collection;

/**
 * Repository contract for querying and persisting ApiClient records.
 */
interface ApiClientRepositoryInterface
{
    /**
     * List API clients owned by a user.
     * @param int $userId
     * @return Collection
     */
    public function listByUser(int $userId): Collection;

    /**
     * Create a new API client record.
     * @param array $data
     * @return ApiClient
     */
    public function create(array $data): ApiClient;

    /**
     * Find an API client by ID.
     * @param int $id
     * @return ?ApiClient
     */
    public function find(int $id): ?ApiClient;
}
