<?php

namespace App\Services\Gateway\ProviderModel;

use App\Models\ProviderModel;
use Illuminate\Support\Collection;

/**
 * Service layer for provider model.
 */
interface ProviderModelServiceInterface
{
    /**
     * List by provider.
     * @param int $providerId
     * @return Collection
     */
    public function listByProvider(int $providerId): Collection;

    /**
     * Create Provider model.
     * @param array $data
     * @return ProviderModel
     */
    public function create(array $data): ProviderModel;
}
