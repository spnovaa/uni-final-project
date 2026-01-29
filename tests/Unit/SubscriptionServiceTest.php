<?php

namespace Tests\Unit;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Billing\Subscription\SubscriptionServiceInterface;
use App\Services\Billing\Wallet\WalletServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribe_creates_subscription_and_debits_wallet(): void
    {
        $user = User::factory()->create();
        $plan = SubscriptionPlan::factory()->create([
            'price' => 10.00,
            'period' => 'monthly',
        ]);

        $wallets = app(WalletServiceInterface::class);
        $wallets->topup($user, 20.00);

        $service = app(SubscriptionServiceInterface::class);
        $subscription = $service->subscribe($user, $plan);

        $this->assertSame('active', $subscription->status);
        $this->assertSame($plan->id, $subscription->plan_id);

        $wallet = $wallets->getOrCreate($user);
        $this->assertSame(10.00, (float) $wallet->balance);
    }
}
