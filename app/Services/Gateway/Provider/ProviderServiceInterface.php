<?php

namespace App\Services\Gateway\Provider;

use App\Models\Provider;
use Illuminate\Support\Collection;

/**
 * Provider service contract.
 *
 * Providers define the upstream endpoints and credentials used by the gateway.
 */
interface ProviderServiceInterface
{
    /**
     * List configured providers (implementations may use caching).
     * @return Collection
     */
    public function list(): Collection;

    /**
     * Create a provider configuration.
     * @param array $data
     * @return Provider
     */
    public function create(array $data): Provider;

    /**
     * Find a provider by ID or throw when not found.
     * @param int $id
     * @return Provider
     */
    public function findOrFail(int $id): Provider;
}
