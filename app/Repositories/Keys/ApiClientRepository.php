<?php

namespace App\Repositories\Keys;

use App\Models\ApiClient;
use Illuminate\Support\Collection;

class ApiClientRepository implements ApiClientRepositoryInterface
{
    public function listByUser(int $userId): Collection
    {
        return ApiClient::query()
            ->where('user_id', $userId)
            ->orderBy('id')
            ->get();
    }

    public function create(array $data): ApiClient
    {
        return ApiClient::query()->create($data);
    }

    public function find(int $id): ?ApiClient
    {
        return ApiClient::query()->find($id);
    }
}
