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
 * Wallet billing service.
 *
 * Provides atomic wallet operations (top-up, debit) and transaction history used by the
 * gateway billing pipeline and reporting.
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
     * Get the user's wallet or create one on first access.
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
     * Top up wallet balance.
     *
     * Validates the amount, updates the wallet balance inside a DB transaction,
     * and records a credit transaction.
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
     * Debit wallet balance.
     *
     * Validates the amount, checks available balance (unless `allowNegative`), updates the wallet
     * inside a DB transaction, and records a debit transaction.
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
     * List wallet transactions.
     *
     * Ensures the wallet exists and returns the most recent transactions up to the limit.
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
