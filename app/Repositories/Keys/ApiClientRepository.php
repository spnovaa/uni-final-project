<?php

namespace App\Repositories\Keys;

use App\Models\ApiClient;
use Illuminate\Support\Collection;

/**
 * Persistence layer for api client.
 */
class ApiClientRepository implements ApiClientRepositoryInterface
{
    /**
     * List by user.
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
     * Create API client.
     * @param array $data
     * @return ApiClient
     */
    public function create(array $data): ApiClient
    {
        return ApiClient::query()->create($data);
    }

    /**
     * Find.
     * @param int $id
     * @return ?ApiClient
     */
    public function find(int $id): ?ApiClient
    {
        return ApiClient::query()->find($id);
    }
}
