<?php

namespace App\Repositories\Gateway;

use App\Models\Provider;
use Illuminate\Support\Collection;

/**
 * Repository for querying and persisting Provider records.
 */
class ProviderRepository implements ProviderRepositoryInterface
{
    /**
     * List configured providers.
     * @return Collection
     */
    public function list(): Collection
    {
        return Provider::query()->orderBy('id')->get();
    }

    /**
     * Create a new provider record.
     * @param array $data
     * @return Provider
     */
    public function create(array $data): Provider
    {
        return Provider::query()->create($data);
    }

    /**
     * Find a provider by ID.
     * @param int $id
     * @return ?Provider
     */
    public function find(int $id): ?Provider
    {
        return Provider::query()->find($id);
    }
}
