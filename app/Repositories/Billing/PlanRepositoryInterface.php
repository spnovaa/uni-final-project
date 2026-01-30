<?php

namespace App\Repositories\Billing;

use App\Models\SubscriptionPlan;
use Illuminate\Support\Collection;

/**
 * Repository contract for querying and persisting SubscriptionPlan records.
 */
interface PlanRepositoryInterface
{
    /**
     * List active subscription plans.
     * @return Collection
     */
    public function listActive(): Collection;

    /**
     * Create a subscription plan record.
     * @param array $data
     * @return SubscriptionPlan
     */
    public function create(array $data): SubscriptionPlan;

    /**
     * Find a subscription plan by ID.
     * @param int $id
     * @return ?SubscriptionPlan
     */
    public function find(int $id): ?SubscriptionPlan;
}
