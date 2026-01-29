<?php

namespace App\Repositories\Gateway;

use App\Models\ProviderModel;
use Illuminate\Support\Collection;

interface ProviderModelRepositoryInterface
{
    public function listByProvider(int $providerId): Collection;

    public function create(array $data): ProviderModel;
}
