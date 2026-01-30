<?php

namespace App\Services\Billing\Plan;

use App\Models\SubscriptionPlan;
use Illuminate\Support\Collection;

/**
 * Service layer for plan.
 */
interface PlanServiceInterface
{
    /**
     * List active.
     * @return Collection
     */
    public function listActive(): Collection;

    /**
     * Create Plan.
     * @param array $data
     * @return SubscriptionPlan
     */
    public function create(array $data): SubscriptionPlan;

    /**
     * Find or fail.
     * @param int $id
     * @return SubscriptionPlan
     */
    public function findOrFail(int $id): SubscriptionPlan;
}
