<?php

namespace App\Services\Gateway\ProviderModel;

use App\Models\ProviderModel;
use Illuminate\Support\Collection;

/**
 * Provider model service contract.
 *
 * Provider models store pricing/capabilities for a provider's billable model key.
 */
interface ProviderModelServiceInterface
{
    /**
     * List models for a provider (implementations may use caching).
     * @param int $providerId
     * @return Collection
     */
    public function listByProvider(int $providerId): Collection;

    /**
     * Create a provider model configuration.
     * @param array $data
     * @return ProviderModel
     */
    public function create(array $data): ProviderModel;
}
