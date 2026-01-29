<?php

namespace App\Repositories\Keys;

use App\Models\ApiKey;
use Illuminate\Support\Collection;

class ApiKeyRepository implements ApiKeyRepositoryInterface
{
    public function listByClient(int $clientId): Collection
    {
        return ApiKey::query()
            ->where('api_client_id', $clientId)
            ->orderBy('id')
            ->get();
    }

    public function create(array $data): ApiKey
    {
        return ApiKey::query()->create($data);
    }

    public function find(int $id): ?ApiKey
    {
        return ApiKey::query()->find($id);
    }

    public function save(ApiKey $apiKey): ApiKey
    {
        $apiKey->save();

        return $apiKey;
    }
}
