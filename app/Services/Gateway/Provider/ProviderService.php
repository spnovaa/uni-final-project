<?php

namespace App\Services\Gateway\Provider;

use App\Models\Provider;
use App\Repositories\Gateway\ProviderRepositoryInterface;
use App\Services\Cache\CacheServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Provider configuration service with cache-aside reads.
 *
 * Providers (OpenAI-compatible or native) are stored in the database. This service caches
 * provider lists and per-provider config for fast routing in the gateway pipeline.
 */
class ProviderService implements ProviderServiceInterface
{
    /**
     * Create a new instance.
     * @param ProviderRepositoryInterface $providers
     * @param CacheServiceInterface $cache
     * @return void
     */
    public function __construct(
        private readonly ProviderRepositoryInterface $providers,
        private readonly CacheServiceInterface $cache,
    )
    {
    }

    /**
     * List providers, served from cache when available.
     * @return Collection
     */
    public function list(): Collection
    {
        $ttl = $this->cache->ttl('providers', 300);
        $key = $this->cache->key('providers', 'all');

        return $this->cache->remember($key, $ttl, function () {
            return $this->providers->list();
        });
    }

    /**
     * Create a provider and invalidate cached provider lists/config.
     *
     * Persists sensitive connection settings (API key/timeout) in `config_encrypted`.
     * @param array $data
     * @return Provider
     */
    public function create(array $data): Provider
    {
        $config = array_filter([
            'api_key' => $data['api_key'] ?? null,
            'timeout' => $data['timeout'] ?? null,
        ], fn ($value) => ! is_null($value));

        $provider = $this->providers->create([
            'name' => $data['name'],
            'type' => $data['type'] ?? 'openai_compatible',
            'base_url' => $data['base_url'] ?? null,
            'status' => $data['status'] ?? 'active',
            'priority' => $data['priority'] ?? 0,
            'config_encrypted' => $config ?: null,
        ]);

        $this->cache->forget($this->cache->key('providers', 'all'));
        $this->cache->forget($this->cache->key('providers', 'config', $provider->name));

        return $provider;
    }

    /**
     * Find a provider by ID or throw a validation exception when missing.
     * @param int $id
     * @return Provider
     */
    public function findOrFail(int $id): Provider
    {
        $provider = $this->providers->find($id);

        if (! $provider) {
            throw ValidationException::withMessages([
                'provider_id' => ['Provider not found.'],
            ]);
        }

        return $provider;
    }
}
