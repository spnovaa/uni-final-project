<?php

namespace App\Domains\Gateway\Services;

use App\Models\ProviderModel;
use App\Models\RoutingRule;
use App\Services\Cache\CacheServiceInterface;
use Illuminate\Support\Facades\Schema;

/**
 * Resolve routing decisions for provider/model selection.
 *
 * This service resolves a requested provider+model into an active ProviderModel record and
 * supports a simple routing rule mechanism (extensible for advanced strategies).
 */
class ProviderRouter
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
     * Resolve an active ProviderModel for a given provider name and model key.
     *
     * Uses cache-aside to avoid repeated database lookups during high traffic.
     * @param ?string $providerName
     * @param ?string $modelKey
     * @return ?ProviderModel
     */
    public function resolveProviderModel(?string $providerName, ?string $modelKey): ?ProviderModel
    {
        if (empty($providerName) || empty($modelKey) || ! Schema::hasTable('provider_models')) {
            return null;
        }

        $cacheKey = $this->cache->key('provider_models', $providerName, $modelKey);
        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $model = ProviderModel::query()
            ->with('provider')
            ->where('model_key', $modelKey)
            ->where('status', 'active')
            ->whereHas('provider', function ($query) use ($providerName) {
                $query->where('name', $providerName)->where('status', 'active');
            })
            ->first();

        if ($model) {
            $ttl = $this->cache->ttl('provider_models', 300);
            $this->cache->put($cacheKey, $model, $ttl);
        }

        return $model;
    }

    /**
     * Resolve the active routing rule (currently global).
     *
     * The current implementation returns the first active rule; it can be expanded to use
     * `modelKey` or other request attributes.
     * @param ?string $modelKey
     * @return ?RoutingRule
     */
    public function resolveRoutingRule(?string $modelKey): ?RoutingRule
    {
        if (! Schema::hasTable('routing_rules')) {
            return null;
        }

        return RoutingRule::query()
            ->where('status', 'active')
            ->first();
    }
}
