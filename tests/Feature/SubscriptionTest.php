<?php

namespace Tests\Feature;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_subscribe_and_cancel(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $plan = SubscriptionPlan::factory()->create([
            'price' => 10.00,
            'period' => 'monthly',
        ]);

        $this->postJson('/api/v1/wallet/topup', [
            'amount' => 20.00,
        ])->assertOk();

        $subscribe = $this->postJson('/api/v1/subscriptions', [
            'plan_id' => $plan->id,
        ]);

        $subscribe->assertCreated()
            ->assertJsonPath('status', 'active');

        $current = $this->getJson('/api/v1/subscriptions/current');
        $current->assertOk()
            ->assertJsonPath('plan.id', $plan->id);

        $cancel = $this->postJson('/api/v1/subscriptions/cancel');
        $cancel->assertOk()
            ->assertJsonPath('status', 'canceled');
    }
}
