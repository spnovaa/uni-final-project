<?php

namespace App\Repositories\Gateway;

use App\Models\ProviderModel;
use Illuminate\Support\Collection;

class ProviderModelRepository implements ProviderModelRepositoryInterface
{
    public function listByProvider(int $providerId): Collection
    {
        return ProviderModel::query()
            ->where('provider_id', $providerId)
            ->orderBy('id')
            ->get();
    }

    public function create(array $data): ProviderModel
    {
        return ProviderModel::query()->create($data);
    }
}
