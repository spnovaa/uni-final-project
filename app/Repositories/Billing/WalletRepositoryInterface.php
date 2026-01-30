<?php

namespace App\Repositories\Billing;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Collection;

/**
 * Repository contract for querying and persisting Wallet and WalletTransaction records.
 */
interface WalletRepositoryInterface
{
    /**
     * Find a user's wallet by user ID.
     * @param int $userId
     * @return ?Wallet
     */
    public function findByUserId(int $userId): ?Wallet;

    /**
     * Create a new wallet for a user.
     * @param int $userId
     * @param string $currency
     * @return Wallet
     */
    public function createForUser(int $userId, string $currency): Wallet;

    /**
     * Persist changes to a wallet model.
     * @param Wallet $wallet
     * @return Wallet
     */
    public function save(Wallet $wallet): Wallet;

    /**
     * Create and attach a wallet ledger transaction to the wallet.
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
     * List recent wallet transactions for a wallet.
     * @param Wallet $wallet
     * @param int $limit
     * @return Collection
     */
    public function listTransactions(Wallet $wallet, int $limit = 50): Collection;
}
