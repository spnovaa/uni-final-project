<?php

namespace App\Services\Billing\Subscription;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;

interface SubscriptionServiceInterface
{
    public function subscribe(User $user, SubscriptionPlan $plan): Subscription;

    public function current(User $user): ?Subscription;

    public function cancel(User $user): ?Subscription;
}
