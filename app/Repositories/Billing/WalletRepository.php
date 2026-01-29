<?php

namespace App\Repositories\Billing;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Collection;

class WalletRepository implements WalletRepositoryInterface
{
    public function findByUserId(int $userId): ?Wallet
    {
        return Wallet::query()->where('user_id', $userId)->first();
    }

    public function createForUser(int $userId, string $currency): Wallet
    {
        return Wallet::query()->create([
            'user_id' => $userId,
            'balance' => 0,
            'currency' => $currency,
        ]);
    }

    public function save(Wallet $wallet): Wallet
    {
        $wallet->save();

        return $wallet;
    }

    public function addTransaction(
        Wallet $wallet,
        string $type,
        float $amount,
        ?string $reason = null,
        ?string $refType = null,
        ?int $refId = null,
        ?array $meta = null
    ): WalletTransaction {
        return $wallet->transactions()->create([
            'type' => $type,
            'amount' => $amount,
            'reason' => $reason,
            'ref_type' => $refType,
            'ref_id' => $refId,
            'meta' => $meta,
        ]);
    }

    public function listTransactions(Wallet $wallet, int $limit = 50): Collection
    {
        return $wallet->transactions()
            ->latest('id')
            ->limit($limit)
            ->get();
    }
}
