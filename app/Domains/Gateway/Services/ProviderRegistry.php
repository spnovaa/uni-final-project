<?php

namespace App\Domains\Gateway\Services;

use App\Models\Provider;
use Illuminate\Support\Facades\Schema;
use App\Services\Cache\CacheServiceInterface;

/**
 * Class ProviderRegistry.
 */
class ProviderRegistry
{
    /**
     * Create a new instance.
     * @param CacheServiceInterface $cache
     * @return void
     */
    public function __construct(private readonly CacheServiceInterface $cache)
    {
    }

    /**
     * Get provider config.
     * @param ?string $providerName
     * @return array
     */
    public function getProviderConfig(?string $providerName = null): array
    {
        $providerName = $providerName ?: config('gateway.default_provider');
        $config = config('gateway.providers.'.$providerName, []);

        if (Schema::hasTable('providers')) {
            $cacheKey = $this->cache->key('providers', 'config', $providerName);
            $ttl = $this->cache->ttl('provider_config', 300);

            $cached = $this->cache->remember($cacheKey, $ttl, function () use ($providerName, $config) {
                $provider = Provider::query()
                    ->where('name', $providerName)
                    ->where('status', 'active')
                    ->first();

                if (! $provider) {
                    return null;
                }

                $encrypted = $provider->config_encrypted ?? [];

                return [
                    'name' => $provider->name,
                    'type' => $provider->type,
                    'base_url' => $provider->base_url ?: ($encrypted['base_url'] ?? ($config['base_url'] ?? null)),
                    'api_key' => $encrypted['api_key'] ?? ($config['api_key'] ?? null),
                    'timeout' => $encrypted['timeout'] ?? ($config['timeout'] ?? 60),
                    'provider_id' => $provider->id,
                ];
            });

            if ($cached) {
                return $cached;
            }
        }

        return array_merge(
            [
                'name' => $providerName,
                'provider_id' => null,
            ],
            $config,
        );
    }
}
