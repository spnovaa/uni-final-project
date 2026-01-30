<?php

namespace App\Repositories\Billing;

use App\Models\Subscription;

/**
 * Repository contract for querying and persisting Subscription records.
 */
interface SubscriptionRepositoryInterface
{
    /**
     * Create a new subscription record.
     * @param array $data
     * @return Subscription
     */
    public function create(array $data): Subscription;

    /**
     * Get the current (most recent active) subscription for a user, if any.
     * @param int $userId
     * @return ?Subscription
     */
    public function currentForUser(int $userId): ?Subscription;

    /**
     * Persist changes to a subscription model.
     * @param Subscription $subscription
     * @return Subscription
     */
    public function save(Subscription $subscription): Subscription;
}
