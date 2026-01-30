<?php

namespace App\Services\Gateway\Provider;

use App\Models\Provider;
use Illuminate\Support\Collection;

/**
 * Service layer for provider.
 */
interface ProviderServiceInterface
{
    /**
     * List Providers.
     * @return Collection
     */
    public function list(): Collection;

    /**
     * Create Provider.
     * @param array $data
     * @return Provider
     */
    public function create(array $data): Provider;

    /**
     * Find or fail.
     * @param int $id
     * @return Provider
     */
    public function findOrFail(int $id): Provider;
}
