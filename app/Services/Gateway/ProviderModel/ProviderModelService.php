<?php

namespace App\Services\Gateway\ProviderModel;

use App\Models\ProviderModel;
use App\Repositories\Gateway\ProviderModelRepositoryInterface;
use Illuminate\Support\Collection;

class ProviderModelService implements ProviderModelServiceInterface
{
    public function __construct(private readonly ProviderModelRepositoryInterface $models)
    {
    }

    public function listByProvider(int $providerId): Collection
    {
        return $this->models->listByProvider($providerId);
    }

    public function create(array $data): ProviderModel
    {
        return $this->models->create($data);
    }
}
