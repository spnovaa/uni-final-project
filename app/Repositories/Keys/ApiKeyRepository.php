<?php

namespace App\Repositories\Keys;

use App\Models\ApiKey;
use Illuminate\Support\Collection;

/**
 * Repository for querying and persisting ApiKey records.
 */
class ApiKeyRepository implements ApiKeyRepositoryInterface
{
    /**
     * List API keys for a given API client.
     * @param int $clientId
     * @return Collection
     */
    public function listByClient(int $clientId): Collection
    {
        return ApiKey::query()
            ->where('api_client_id', $clientId)
            ->orderBy('id')
            ->get();
    }

    /**
     * Create a new API key record.
     * @param array $data
     * @return ApiKey
     */
    public function create(array $data): ApiKey
    {
        return ApiKey::query()->create($data);
    }

    /**
     * Find an API key by ID.
     * @param int $id
     * @return ?ApiKey
     */
    public function find(int $id): ?ApiKey
    {
        return ApiKey::query()->find($id);
    }

    /**
     * Persist changes to an API key model.
     * @param ApiKey $apiKey
     * @return ApiKey
     */
    public function save(ApiKey $apiKey): ApiKey
    {
        $apiKey->save();

        return $apiKey;
    }
}
