<?php

namespace App\Services\Billing\Subscription;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;

/**
 * Service layer for subscription.
 */
interface SubscriptionServiceInterface
{
    /**
     * Subscribe a user to a plan.
     * @param User $user
     * @param SubscriptionPlan $plan
     * @return Subscription
     */
    public function subscribe(User $user, SubscriptionPlan $plan): Subscription;

    /**
     * Current.
     * @param User $user
     * @return ?Subscription
     */
    public function current(User $user): ?Subscription;

    /**
     * Cancel a subscription.
     * @param User $user
     * @return ?Subscription
     */
    public function cancel(User $user): ?Subscription;
}
