<?php

namespace App\Services\Billing\Wallet;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Collection;

interface WalletServiceInterface
{
    public function getOrCreate(User $user, ?string $currency = null): Wallet;

    public function topup(User $user, float $amount, ?string $reason = null, ?array $meta = null): WalletTransaction;

    public function debit(
        User $user,
        float $amount,
        string $reason,
        ?array $meta = null,
        bool $allowNegative = false
    ): WalletTransaction;

    public function transactions(User $user, int $limit = 50): Collection;
}
