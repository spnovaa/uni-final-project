<?php

namespace App\Repositories\Gateway;

use App\Models\Provider;
use Illuminate\Support\Collection;

/**
 * Persistence layer for provider.
 */
class ProviderRepository implements ProviderRepositoryInterface
{
    /**
     * List.
     * @return Collection
     */
    public function list(): Collection
    {
        return Provider::query()->orderBy('id')->get();
    }

    /**
     * Create.
     * @param array $data
     * @return Provider
     */
    public function create(array $data): Provider
    {
        return Provider::query()->create($data);
    }

    /**
     * Find.
     * @param int $id
     * @return ?Provider
     */
    public function find(int $id): ?Provider
    {
        return Provider::query()->find($id);
    }
}
