<?php

namespace App\Services\Gateway\Provider;

use App\Models\Provider;
use App\Repositories\Gateway\ProviderRepositoryInterface;
use App\Services\Cache\CacheServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProviderService implements ProviderServiceInterface
{
    public function __construct(
        private readonly ProviderRepositoryInterface $providers,
        private readonly CacheServiceInterface $cache,
    )
    {
    }

    public function list(): Collection
    {
        $ttl = $this->cache->ttl('providers', 300);
        $key = $this->cache->key('providers', 'all');

        return $this->cache->remember($key, $ttl, function () {
            return $this->providers->list();
        });
    }

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
