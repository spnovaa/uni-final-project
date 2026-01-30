<?php

namespace App\Repositories\Billing;

use App\Models\SubscriptionPlan;
use Illuminate\Support\Collection;

/**
 * Repository for querying and persisting SubscriptionPlan records.
 */
class PlanRepository implements PlanRepositoryInterface
{
    /**
     * List active subscription plans.
     * @return Collection
     */
    public function listActive(): Collection
    {
        return SubscriptionPlan::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get();
    }

    /**
     * Create a subscription plan record.
     * @param array $data
     * @return SubscriptionPlan
     */
    public function create(array $data): SubscriptionPlan
    {
        return SubscriptionPlan::query()->create($data);
    }

    /**
     * Find a subscription plan by ID.
     * @param int $id
     * @return ?SubscriptionPlan
     */
    public function find(int $id): ?SubscriptionPlan
    {
        return SubscriptionPlan::query()->find($id);
    }
}
