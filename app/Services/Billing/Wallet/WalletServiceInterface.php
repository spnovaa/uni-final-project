<?php

namespace App\Services\Billing\Wallet;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Collection;

/**
 * Service layer for wallet.
 */
interface WalletServiceInterface
{
    /**
     * Get or create.
     * @param User $user
     * @param ?string $currency
     * @return Wallet
     */
    public function getOrCreate(User $user, ?string $currency = null): Wallet;

    /**
     * Topup.
     * @param User $user
     * @param float $amount
     * @param ?string $reason
     * @param ?array $meta
     * @return WalletTransaction
     */
    public function topup(User $user, float $amount, ?string $reason = null, ?array $meta = null): WalletTransaction;

    /**
     * Debit.
     * @param User $user
     * @param float $amount
     * @param string $reason
     * @param ?array $meta
     * @param bool $allowNegative
     * @return WalletTransaction
     */
    public function debit(
        User $user,
        float $amount,
        string $reason,
        ?array $meta = null,
        bool $allowNegative = false
    ): WalletTransaction;

    /**
     * Transactions.
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function transactions(User $user, int $limit = 50): Collection;
}
