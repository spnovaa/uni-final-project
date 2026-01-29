<?php

namespace App\Repositories\Keys;

use App\Models\ApiClient;
use Illuminate\Support\Collection;

interface ApiClientRepositoryInterface
{
    public function listByUser(int $userId): Collection;

    public function create(array $data): ApiClient;

    public function find(int $id): ?ApiClient;
}
