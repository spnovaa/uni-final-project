<?php

namespace App\Repositories\Billing;

use App\Models\Subscription;

/**
 * Persistence layer for subscription.
 */
class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * Create Subscription.
     * @param array $data
     * @return Subscription
     */
    public function create(array $data): Subscription
    {
        return Subscription::query()->create($data);
    }

    /**
     * Current for user.
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
     * Save.
     * @param Subscription $subscription
     * @return Subscription
     */
    public function save(Subscription $subscription): Subscription
    {
        $subscription->save();

        return $subscription;
    }
}
