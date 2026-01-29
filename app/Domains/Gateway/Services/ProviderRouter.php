<?php

namespace App\Domains\Gateway\Services;

use App\Models\ProviderModel;
use App\Models\RoutingRule;
use App\Services\Cache\CacheServiceInterface;
use Illuminate\Support\Facades\Schema;

class ProviderRouter
{
    public function __construct(private readonly CacheServiceInterface $cache)
    {
    }

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
