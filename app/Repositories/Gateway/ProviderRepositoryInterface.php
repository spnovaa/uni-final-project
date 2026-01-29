<?php

namespace App\Repositories\Gateway;

use App\Models\Provider;
use Illuminate\Support\Collection;

interface ProviderRepositoryInterface
{
    public function list(): Collection;

    public function create(array $data): Provider;

    public function find(int $id): ?Provider;
}
