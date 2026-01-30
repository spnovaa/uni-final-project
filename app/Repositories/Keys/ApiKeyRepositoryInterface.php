<?php

namespace App\Repositories\Keys;

use App\Models\ApiKey;
use Illuminate\Support\Collection;

/**
 * Repository contract for querying and persisting ApiKey records.
 */
interface ApiKeyRepositoryInterface
{
    /**
     * List API keys for a given API client.
     * @param int $clientId
     * @return Collection
     */
    public function listByClient(int $clientId): Collection;

    /**
     * Create a new API key record.
     * @param array $data
     * @return ApiKey
     */
    public function create(array $data): ApiKey;

    /**
     * Find an API key by ID.
     * @param int $id
     * @return ?ApiKey
     */
    public function find(int $id): ?ApiKey;

    /**
     * Persist changes to an API key model.
     * @param ApiKey $apiKey
     * @return ApiKey
     */
    public function save(ApiKey $apiKey): ApiKey;
}
