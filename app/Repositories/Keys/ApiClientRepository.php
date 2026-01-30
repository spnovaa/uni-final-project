<?php

namespace App\Repositories\Keys;

use App\Models\ApiClient;
use Illuminate\Support\Collection;

/**
 * Repository for querying and persisting ApiClient records.
 */
class ApiClientRepository implements ApiClientRepositoryInterface
{
    /**
     * List API clients owned by a user.
     * @param int $userId
     * @return Collection
     */
    public function listByUser(int $userId): Collection
    {
        return ApiClient::query()
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get();
    }

    /**
     * Create a new API client record.
     * @param array $data
     * @return ApiClient
     */
    public function create(array $data): ApiClient
    {
        return ApiClient::query()->create($data);
    }

    /**
     * Find an API client by ID.
     * @param int $id
     * @return ?ApiClient
     */
    public function find(int $id): ?ApiClient
    {
        return ApiClient::query()->find($id);
    }
}
