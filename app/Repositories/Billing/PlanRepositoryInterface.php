<?php

namespace App\Repositories\Billing;

use App\Models\SubscriptionPlan;
use Illuminate\Support\Collection;

/**
 * Persistence layer for plan.
 */
interface PlanRepositoryInterface
{
    /**
     * List active.
     * @return Collection
     */
    public function listActive(): Collection;

    /**
     * Create.
     * @param array $data
     * @return SubscriptionPlan
     */
    public function create(array $data): SubscriptionPlan;

    /**
     * Find.
     * @param int $id
     * @return ?SubscriptionPlan
     */
    public function find(int $id): ?SubscriptionPlan;
}
