<?php

namespace App\Repositories\Billing;

use App\Models\Subscription;

interface SubscriptionRepositoryInterface
{
    public function create(array $data): Subscription;

    public function currentForUser(int $userId): ?Subscription;

    public function save(Subscription $subscription): Subscription;
}
