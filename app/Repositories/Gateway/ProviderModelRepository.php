<?php

namespace App\Repositories\Gateway;

use App\Models\ProviderModel;
use Illuminate\Support\Collection;

/**
 * Persistence layer for provider model.
 */
class ProviderModelRepository implements ProviderModelRepositoryInterface
{
    /**
     * List by provider.
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
     * Create Provider model.
     * @param array $data
     * @return ProviderModel
     */
    public function create(array $data): ProviderModel
    {
        return ProviderModel::query()->create($data);
    }
}
