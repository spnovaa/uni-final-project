<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\Billing\Wallet\WalletServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_topup_increases_balance(): void
    {
        $user = User::factory()->create();
        $service = app(WalletServiceInterface::class);

        $service->topup($user, 25.50);

        $wallet = $service->getOrCreate($user);
        $this->assertSame(25.50, (float) $wallet->balance);
    }

    public function test_debit_reduces_balance(): void
    {
        $user = User::factory()->create();
        $service = app(WalletServiceInterface::class);

        $service->topup($user, 50.00);
        $service->debit($user, 20.00, 'test');

        $wallet = $service->getOrCreate($user);
        $this->assertSame(30.00, (float) $wallet->balance);
    }

    public function test_debit_throws_when_insufficient_balance(): void
    {
        $user = User::factory()->create();
        $service = app(WalletServiceInterface::class);

        $this->expectException(ValidationException::class);
        $service->debit($user, 10.00, 'test');
    }
}
