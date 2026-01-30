<?php

namespace App\Services\Billing\Wallet;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Repositories\Billing\WalletRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Service layer for wallet.
 */
class WalletService implements WalletServiceInterface
{
    /**
     * Create a new instance.
     * @param WalletRepositoryInterface $wallets
     * @return void
     */
    public function __construct(private readonly WalletRepositoryInterface $wallets)
    {
    }

    /**
     * Get or create.
     * @param User $user
     * @param ?string $currency
     * @return Wallet
     */
    public function getOrCreate(User $user, ?string $currency = null): Wallet
    {
        $wallet = $this->wallets->findByUserId($user->id);

        if ($wallet) {
            return $wallet;
        }

        return $this->wallets->createForUser(
            $user->id,
            $currency ?: 'USD'
        );
    }

    /**
     * Topup.
     * @param User $user
     * @param float $amount
     * @param ?string $reason
     * @param ?array $meta
     * @return WalletTransaction
     */
    public function topup(User $user, float $amount, ?string $reason = null, ?array $meta = null): WalletTransaction
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Top-up amount must be greater than zero.'],
            ]);
        }

        return DB::transaction(function () use ($user, $amount, $reason, $meta) {
            $wallet = $this->getOrCreate($user);
            $wallet->balance = (float) $wallet->balance + $amount;
            $this->wallets->save($wallet);

            return $this->wallets->addTransaction(
                $wallet,
                'credit',
                $amount,
                $reason ?: 'topup',
                null,
                null,
                $meta
            );
        });
    }

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
    ): WalletTransaction {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Debit amount must be greater than zero.'],
            ]);
        }

        return DB::transaction(function () use ($user, $amount, $reason, $meta, $allowNegative) {
            $wallet = $this->getOrCreate($user);
            $currentBalance = (float) $wallet->balance;

            if (! $allowNegative && $currentBalance < $amount) {
                throw ValidationException::withMessages([
                    'balance' => ['Insufficient wallet balance.'],
                ]);
            }

            $wallet->balance = $currentBalance - $amount;
            $this->wallets->save($wallet);

            return $this->wallets->addTransaction(
                $wallet,
                'debit',
                $amount,
                $reason,
                null,
                null,
                $meta
            );
        });
    }

    /**
     * Transactions.
     * @param User $user
     * @param int $limit
     * @return Collection
     */
    public function transactions(User $user, int $limit = 50): Collection
    {
        $wallet = $this->getOrCreate($user);

        return $this->wallets->listTransactions($wallet, $limit);
    }
}
