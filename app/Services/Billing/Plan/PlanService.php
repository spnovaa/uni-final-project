<?php

namespace App\Services\Billing\Plan;

use App\Models\SubscriptionPlan;
use App\Repositories\Billing\PlanRepositoryInterface;
use App\Services\Cache\CacheServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PlanService implements PlanServiceInterface
{
    public function __construct(
        private readonly PlanRepositoryInterface $plans,
        private readonly CacheServiceInterface $cache,
    )
    {
    }

    public function listActive(): Collection
    {
        $ttl = $this->cache->ttl('plans', 300);
        $key = $this->cache->key('plans', 'active');

        return $this->cache->remember($key, $ttl, function () {
            return $this->plans->listActive();
        });
    }

    public function create(array $data): SubscriptionPlan
    {
        $plan = $this->plans->create($data);
        $this->cache->forget($this->cache->key('plans', 'active'));

        return $plan;
    }

    public function findOrFail(int $id): SubscriptionPlan
    {
        $plan = $this->plans->find($id);

        if (! $plan) {
            throw ValidationException::withMessages([
                'plan_id' => ['Plan not found.'],
            ]);
        }

        return $plan;
    }
}
