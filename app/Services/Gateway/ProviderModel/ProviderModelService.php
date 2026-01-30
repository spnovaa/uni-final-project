<?php

namespace App\Services\Gateway\ProviderModel;

use App\Models\ProviderModel;
use App\Repositories\Gateway\ProviderModelRepositoryInterface;
use App\Services\Cache\CacheServiceInterface;
use Illuminate\Support\Collection;

/**
 * Service layer for provider model.
 */
class ProviderModelService implements ProviderModelServiceInterface
{
    /**
     * Create a new instance.
     * @param ProviderModelRepositoryInterface $models
     * @param CacheServiceInterface $cache
     * @return void
     */
    public function __construct(
        private readonly ProviderModelRepositoryInterface $models,
        private readonly CacheServiceInterface $cache,
    )
    {
    }

    /**
     * List by provider.
     * @param int $providerId
     * @return Collection
     */
    public function listByProvider(int $providerId): Collection
    {
        $ttl = $this->cache->ttl('provider_models', 300);
        $key = $this->cache->key('provider_models', 'list', (string) $providerId);

        return $this->cache->remember($key, $ttl, function () use ($providerId) {
            return $this->models->listByProvider($providerId);
        });
    }

    /**
     * Create Provider model.
     * @param array $data
     * @return ProviderModel
     */
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
