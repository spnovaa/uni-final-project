<?php

namespace App\Services\Billing\Wallet;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Collection;

/**
 * Wallet service contract.
 *
 * Implementations must update balances atomically and record ledger entries for reporting.
 */
interface WalletServiceInterface
{
    /**
     * Get the user's wallet or create one on first access.
     * @param User $user
     * @param ?string $currency
     * @return Wallet
     */
    public function getOrCreate(User $user, ?string $currency = null): Wallet;

    /**
     * Top up wallet balance.
     *
     * Implementations should validate the amount and create a credit transaction.
     * @param User $user
     * @param float $amount
     * @param ?string $reason
     * @param ?array $meta
     * @return WalletTransaction
     */
    public function topup(User $user, float $amount, ?string $reason = null, ?array $meta = null): WalletTransaction;

    /**
     * Debit wallet balance.
     *
     * Implementations should validate the amount, enforce balance constraints (unless allowed),
     * and create a debit transaction.
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
     * List wallet transactions.
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function transactions(User $user, int $limit = 50): Collection;
}
