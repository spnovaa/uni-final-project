<?php

namespace App\Services\Billing\Plan;

use App\Models\SubscriptionPlan;
use App\Repositories\Billing\PlanRepositoryInterface;
use App\Services\Cache\CacheServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Subscription plan service with cache-aside reads.
 *
 * Plans are stored in the database and cached for fast listing; writes invalidate cached lists.
 */
class PlanService implements PlanServiceInterface
{
    /**
     * Create a new instance.
     * @param PlanRepositoryInterface $plans
     * @param CacheServiceInterface $cache
     * @return void
     */
    public function __construct(
        private readonly PlanRepositoryInterface $plans,
        private readonly CacheServiceInterface $cache,
    )
    {
    }

    /**
     * List active plans, served from cache when available.
     * @return Collection
     */
    public function listActive(): Collection
    {
        $ttl = $this->cache->ttl('plans', 300);
        $key = $this->cache->key('plans', 'active');

        return $this->cache->remember($key, $ttl, function () {
            return $this->plans->listActive();
        });
    }

    /**
     * Create a plan and invalidate cached plan lists.
     * @param array $data
     * @return SubscriptionPlan
     */
    public function create(array $data): SubscriptionPlan
    {
        $plan = $this->plans->create($data);
        $this->cache->forget($this->cache->key('plans', 'active'));

        return $plan;
    }

    /**
     * Find a plan by ID or throw a validation exception.
     * @param int $id
     * @return SubscriptionPlan
     */
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
