<?php

namespace App\Repositories\Keys;

use App\Models\ApiKey;
use Illuminate\Support\Collection;

interface ApiKeyRepositoryInterface
{
    public function listByClient(int $clientId): Collection;

    public function create(array $data): ApiKey;

    public function find(int $id): ?ApiKey;

    public function save(ApiKey $apiKey): ApiKey;
}
