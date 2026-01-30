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

/**
 * Subscription billing service.
 *
 * Handles subscribing/canceling users to plans and integrates with wallet debiting and audit logs.
 */
class SubscriptionService implements SubscriptionServiceInterface
{
    /**
     * Create a new instance.
     * @param SubscriptionRepositoryInterface $subscriptions
     * @param WalletServiceInterface $wallets
     * @param AuditLogServiceInterface $audit
     * @return void
     */
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptions,
        private readonly WalletServiceInterface $wallets,
        private readonly AuditLogServiceInterface $audit
    ) {
    }

    /**
     * Subscribe a user to a plan.
     *
     * - Prevents multiple simultaneous active subscriptions.
     * - Debits the wallet for paid plans inside a DB transaction.
     * - Creates an active subscription window based on the plan period.
     * - Records an audit event.
     * @param User $user
     * @param SubscriptionPlan $plan
     * @return Subscription
     */
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

    /**
     * Get the current active subscription for a user (if any).
     * @param User $user
     * @return ?Subscription
     */
    public function current(User $user): ?Subscription
    {
        return $this->subscriptions->currentForUser($user->id);
    }

    /**
     * Cancel a subscription.
     *
     * Marks the subscription as canceled and records an audit event. This method does not
     * perform refunds; refunds (if any) must be handled externally.
     * @param User $user
     * @return ?Subscription
     */
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
