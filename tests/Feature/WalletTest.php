<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_and_topup_wallet(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $show = $this->getJson('/api/v1/wallet');
        $show->assertOk()
            ->assertJsonPath('balance', 0);

        $topup = $this->postJson('/api/v1/wallet/topup', [
            'amount' => 25.50,
            'reason' => 'test_topup',
        ]);

        $topup->assertOk()
            ->assertJsonPath('wallet.balance', 25.5)
            ->assertJsonPath('transaction.type', 'credit');

        $transactions = $this->getJson('/api/v1/wallet/transactions?limit=10');
        $transactions->assertOk()
            ->assertJsonFragment(['reason' => 'test_topup']);
    }
}
