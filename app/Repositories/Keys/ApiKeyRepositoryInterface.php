<?php

namespace App\Repositories\Keys;

use App\Models\ApiKey;
use Illuminate\Support\Collection;

/**
 * Persistence layer for api key.
 */
interface ApiKeyRepositoryInterface
{
    /**
     * List by client.
     * @param int $clientId
     * @return Collection
     */
    public function listByClient(int $clientId): Collection;

    /**
     * Create.
     * @param array $data
     * @return ApiKey
     */
    public function create(array $data): ApiKey;

    /**
     * Find.
     * @param int $id
     * @return ?ApiKey
     */
    public function find(int $id): ?ApiKey;

    /**
     * Save.
     * @param ApiKey $apiKey
     * @return ApiKey
     */
    public function save(ApiKey $apiKey): ApiKey;
}
