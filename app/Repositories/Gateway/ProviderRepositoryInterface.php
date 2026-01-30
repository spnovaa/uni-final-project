<?php

namespace App\Repositories\Gateway;

use App\Models\Provider;
use Illuminate\Support\Collection;

/**
 * Repository contract for querying and persisting Provider records.
 */
interface ProviderRepositoryInterface
{
    /**
     * List configured providers.
     * @return Collection
     */
    public function list(): Collection;

    /**
     * Create a new provider record.
     * @param array $data
     * @return Provider
     */
    public function create(array $data): Provider;

    /**
     * Find a provider by ID.
     * @param int $id
     * @return ?Provider
     */
    public function find(int $id): ?Provider;
}
