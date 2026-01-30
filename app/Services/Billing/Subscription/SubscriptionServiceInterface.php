<?php

namespace App\Services\Billing\Subscription;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;

/**
 * Subscription service contract.
 *
 * Implementations define how users subscribe/cancel and how payments are handled.
 */
interface SubscriptionServiceInterface
{
    /**
     * Subscribe a user to a plan.
     *
     * Implementations may debit wallets or integrate with external payments.
     * @param User $user
     * @param SubscriptionPlan $plan
     * @return Subscription
     */
    public function subscribe(User $user, SubscriptionPlan $plan): Subscription;

    /**
     * Get the current active subscription for a user (if any).
     * @param User $user
     * @return ?Subscription
     */
    public function current(User $user): ?Subscription;

    /**
     * Cancel a subscription.
     *
     * Implementations should mark subscriptions canceled and record cancellation time.
     * @param User $user
     * @return ?Subscription
     */
    public function cancel(User $user): ?Subscription;
}
