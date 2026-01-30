<?php

namespace App\Repositories\Gateway;

use App\Models\ProviderModel;
use Illuminate\Support\Collection;

/**
 * Repository contract for querying and persisting ProviderModel records.
 */
interface ProviderModelRepositoryInterface
{
    /**
     * List provider models for a provider.
     * @param int $providerId
     * @return Collection
     */
    public function listByProvider(int $providerId): Collection;

    /**
     * Create a new provider model record.
     * @param array $data
     * @return ProviderModel
     */
    public function create(array $data): ProviderModel;
}
