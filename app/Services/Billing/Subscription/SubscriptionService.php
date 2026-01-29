<?php

namespace App\Services\Billing\Subscription;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Repositories\Billing\SubscriptionRepositoryInterface;
use App\Services\Audit\AuditLogServiceInterface;
use App\Services\Billing\Wallet\WalletServiceInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionService implements SubscriptionServiceInterface
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptions,
        private readonly WalletServiceInterface $wallets,
        private readonly AuditLogServiceInterface $audit
    ) {
    }

    public function subscribe(User $user, SubscriptionPlan $plan): Subscription
    {
        $current = $this->subscriptions->currentForUser($user->id);
        if ($current && $current->status === 'active' && $current->ends_at?->isFuture()) {
            throw ValidationException::withMessages([
                'subscription' => ['An active subscription already exists.'],
            ]);
        }

        return DB::transaction(function () use ($user, $plan) {
            if ((float) $plan->price > 0) {
                $this->wallets->debit(
                    $user,
                    (float) $plan->price,
                    'subscription',
                    ['plan_id' => $plan->id]
                );
            }

            $now = CarbonImmutable::now();
            $endsAt = $plan->period === 'yearly'
                ? $now->addYear()
                : $now->addMonth();

            $subscription = $this->subscriptions->create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => $now,
                'ends_at' => $endsAt,
                'renewal_at' => $endsAt,
            ]);

            $this->audit->record($user, 'subscription.created', $subscription, [
                'plan_id' => $plan->id,
            ]);

            return $subscription;
        });
    }

    public function current(User $user): ?Subscription
    {
        return $this->subscriptions->currentForUser($user->id);
    }

    public function cancel(User $user): ?Subscription
    {
        $subscription = $this->subscriptions->currentForUser($user->id);

        if (! $subscription) {
            return null;
        }

        $subscription->status = 'canceled';
        $subscription->canceled_at = CarbonImmutable::now();
        $this->subscriptions->save($subscription);

        $this->audit->record($user, 'subscription.canceled', $subscription);

        return $subscription;
    }
}
