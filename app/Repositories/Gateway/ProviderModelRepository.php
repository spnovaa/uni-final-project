<?php

namespace App\Repositories\Gateway;

use App\Models\ProviderModel;
use Illuminate\Support\Collection;

/**
 * Repository for querying and persisting ProviderModel records.
 */
class ProviderModelRepository implements ProviderModelRepositoryInterface
{
    /**
     * List provider models for a provider.
     * @param int $providerId
     * @return Collection
     */
    public function listByProvider(int $providerId): Collection
    {
        return ProviderModel::query()
            ->where('provider_id', $providerId)
            ->orderBy('id')
            ->get();
    }

    /**
     * Create a new provider model record.
     * @param array $data
     * @return ProviderModel
     */
    public function create(array $data): ProviderModel
    {
        return ProviderModel::query()->create($data);
    }
}
