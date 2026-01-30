<?php

namespace App\Repositories\Gateway;

use App\Models\Provider;
use Illuminate\Support\Collection;

/**
 * Persistence layer for provider.
 */
interface ProviderRepositoryInterface
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
     * Find.
     * @param int $id
     * @return ?Provider
     */
    public function find(int $id): ?Provider;
}
