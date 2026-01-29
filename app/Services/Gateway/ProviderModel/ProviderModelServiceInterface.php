<?php

namespace App\Services\Gateway\ProviderModel;

use App\Models\ProviderModel;
use Illuminate\Support\Collection;

interface ProviderModelServiceInterface
{
    public function listByProvider(int $providerId): Collection;

    public function create(array $data): ProviderModel;
}
