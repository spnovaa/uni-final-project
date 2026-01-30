<?php

namespace App\Repositories\Billing;

use App\Models\SubscriptionPlan;
use Illuminate\Support\Collection;

/**
 * Persistence layer for plan.
 */
class PlanRepository implements PlanRepositoryInterface
{
    /**
     * List active.
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
     * Create.
     * @param array $data
     * @return SubscriptionPlan
     */
    public function create(array $data): SubscriptionPlan
    {
        return SubscriptionPlan::query()->create($data);
    }

    /**
     * Find.
     * @param int $id
     * @return ?SubscriptionPlan
     */
    public function find(int $id): ?SubscriptionPlan
    {
        return SubscriptionPlan::query()->find($id);
    }
}
