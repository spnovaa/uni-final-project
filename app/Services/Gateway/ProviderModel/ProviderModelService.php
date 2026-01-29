<?php

namespace App\Services\Gateway\ProviderModel;

use App\Models\ProviderModel;
use App\Repositories\Gateway\ProviderModelRepositoryInterface;
use App\Services\Cache\CacheServiceInterface;
use Illuminate\Support\Collection;

class ProviderModelService implements ProviderModelServiceInterface
{
    public function __construct(
        private readonly ProviderModelRepositoryInterface $models,
        private readonly CacheServiceInterface $cache,
    )
    {
    }

    public function listByProvider(int $providerId): Collection
    {
        $ttl = $this->cache->ttl('provider_models', 300);
        $key = $this->cache->key('provider_models', 'list', (string) $providerId);

        return $this->cache->remember($key, $ttl, function () use ($providerId) {
            return $this->models->listByProvider($providerId);
        });
    }

    public function create(array $data): ProviderModel
    {
        $model = $this->models->create($data);

        $providerId = (string) ($model->provider_id ?? ($data['provider_id'] ?? ''));
        if ($providerId !== '') {
            $this->cache->forget($this->cache->key('provider_models', 'list', $providerId));
        }

        $providerName = $data['provider_name'] ?? null;
        if (! $providerName) {
            $providerName = $model->provider()->value('name');
        }

        if ($providerName && ! empty($model->model_key)) {
            $this->cache->forget(
                $this->cache->key('provider_models', (string) $providerName, (string) $model->model_key)
            );
        }

        return $model;
    }
}
