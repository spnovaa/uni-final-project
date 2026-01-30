<?php

namespace App\Services\Billing\Plan;

use App\Models\SubscriptionPlan;
use Illuminate\Support\Collection;

/**
 * Subscription plan service contract.
 *
 * Plans define pricing, included credits, and features for subscriptions.
 */
interface PlanServiceInterface
{
    /**
     * List active plans (implementations may use caching).
     * @return Collection
     */
    public function listActive(): Collection;

    /**
     * Create a subscription plan.
     * @param array $data
     * @return SubscriptionPlan
     */
    public function create(array $data): SubscriptionPlan;

    /**
     * Find a plan by ID or throw when not found.
     * @param int $id
     * @return SubscriptionPlan
     */
    public function findOrFail(int $id): SubscriptionPlan;
}
