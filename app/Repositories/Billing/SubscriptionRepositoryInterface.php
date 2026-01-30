<?php

namespace App\Repositories\Billing;

use App\Models\Subscription;

/**
 * Persistence layer for subscription.
 */
interface SubscriptionRepositoryInterface
{
    /**
     * Create.
     * @param array $data
     * @return Subscription
     */
    public function create(array $data): Subscription;

    /**
     * Current for user.
     * @param int $userId
     * @return ?Subscription
     */
    public function currentForUser(int $userId): ?Subscription;

    /**
     * Save.
     * @param Subscription $subscription
     * @return Subscription
     */
    public function save(Subscription $subscription): Subscription;
}
