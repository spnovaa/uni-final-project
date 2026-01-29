<?php

namespace App\Repositories\Billing;

use App\Models\Subscription;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function create(array $data): Subscription
    {
        return Subscription::query()->create($data);
    }

    public function currentForUser(int $userId): ?Subscription
    {
        return Subscription::query()
            ->with('plan')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->orderByDesc('starts_at')
            ->first();
    }

    public function save(Subscription $subscription): Subscription
    {
        $subscription->save();

        return $subscription;
    }
}
