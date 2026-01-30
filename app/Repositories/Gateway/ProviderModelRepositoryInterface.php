<?php

namespace App\Repositories\Gateway;

use App\Models\ProviderModel;
use Illuminate\Support\Collection;

/**
 * Persistence layer for provider model.
 */
interface ProviderModelRepositoryInterface
{
    /**
     * List by provider.
     * @param int $providerId
     * @return Collection
     */
    public function listByProvider(int $providerId): Collection;

    /**
     * Create.
     * @param array $data
     * @return ProviderModel
     */
    public function create(array $data): ProviderModel;
}
