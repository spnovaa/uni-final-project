<?php

namespace App\Repositories\Billing;

use App\Models\Subscription;

/**
 * Repository for querying and persisting Subscription records.
 */
class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * Create a new subscription record.
     * @param array $data
     * @return Subscription
     */
    public function create(array $data): Subscription
    {
        return Subscription::query()->create($data);
    }

    /**
     * Get the current (most recent active) subscription for a user, if any.
     * @param int $userId
     * @return ?Subscription
     */
    public function currentForUser(int $userId): ?Subscription
    {
        return Subscription::query()
            ->with('plan')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->orderByDesc('starts_at')
            ->first();
    }

    /**
     * Persist changes to a subscription model.
     * @param Subscription $subscription
     * @return Subscription
     */
    public function save(Subscription $subscription): Subscription
    {
        $subscription->save();

        return $subscription;
    }
}
