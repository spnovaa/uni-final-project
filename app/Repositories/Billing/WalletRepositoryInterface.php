<?php

namespace App\Repositories\Billing;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Collection;

/**
 * Persistence layer for wallet.
 */
interface WalletRepositoryInterface
{
    /**
     * Find by user id.
     * @param int $userId
     * @return ?Wallet
     */
    public function findByUserId(int $userId): ?Wallet;

    /**
     * Create for user.
     * @param int $userId
     * @param string $currency
     * @return Wallet
     */
    public function createForUser(int $userId, string $currency): Wallet;

    /**
     * Save.
     * @param Wallet $wallet
     * @return Wallet
     */
    public function save(Wallet $wallet): Wallet;

    /**
     * Add transaction.
     * @param Wallet $wallet
     * @param string $type
     * @param float $amount
     * @param ?string $reason
     * @param ?string $refType
     * @param ?int $refId
     * @param ?array $meta
     * @return WalletTransaction
     */
    public function addTransaction(
        Wallet $wallet,
        string $type,
        float $amount,
        ?string $reason = null,
        ?string $refType = null,
        ?int $refId = null,
        ?array $meta = null
    ): WalletTransaction;

    /**
     * List transactions.
     * @param Wallet $wallet
     * @param int $limit
     * @return Collection
     */
    public function listTransactions(Wallet $wallet, int $limit = 50): Collection;
}
