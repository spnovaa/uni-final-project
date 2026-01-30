<?php

namespace App\Repositories\Keys;

use App\Models\ApiKey;
use Illuminate\Support\Collection;

/**
 * Persistence layer for api key.
 */
class ApiKeyRepository implements ApiKeyRepositoryInterface
{
    /**
     * List by client.
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
     * Create API key.
     * @param array $data
     * @return ApiKey
     */
    public function create(array $data): ApiKey
    {
        return ApiKey::query()->create($data);
    }

    /**
     * Find.
     * @param int $id
     * @return ?ApiKey
     */
    public function find(int $id): ?ApiKey
    {
        return ApiKey::query()->find($id);
    }

    /**
     * Save.
     * @param ApiKey $apiKey
     * @return ApiKey
     */
    public function save(ApiKey $apiKey): ApiKey
    {
        $apiKey->save();

        return $apiKey;
    }
}
