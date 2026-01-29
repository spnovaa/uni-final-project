<?php

namespace App\Repositories\Billing;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Collection;

interface WalletRepositoryInterface
{
    public function findByUserId(int $userId): ?Wallet;

    public function createForUser(int $userId, string $currency): Wallet;

    public function save(Wallet $wallet): Wallet;

    public function addTransaction(
        Wallet $wallet,
        string $type,
        float $amount,
        ?string $reason = null,
        ?string $refType = null,
        ?int $refId = null,
        ?array $meta = null
    ): WalletTransaction;

    public function listTransactions(Wallet $wallet, int $limit = 50): Collection;
}
