<?php

namespace App\Repositories\Gateway;

use App\Models\Provider;
use Illuminate\Support\Collection;

class ProviderRepository implements ProviderRepositoryInterface
{
    public function list(): Collection
    {
        return Provider::query()->orderBy('id')->get();
    }

    public function create(array $data): Provider
    {
        return Provider::query()->create($data);
    }

    public function find(int $id): ?Provider
    {
        return Provider::query()->find($id);
    }
}
