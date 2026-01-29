<?php

namespace App\Services\Billing\Wallet;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Repositories\Billing\WalletRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService implements WalletServiceInterface
{
    public function __construct(private readonly WalletRepositoryInterface $wallets)
    {
    }

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

    public function transactions(User $user, int $limit = 50): Collection
    {
        $wallet = $this->getOrCreate($user);

        return $this->wallets->listTransactions($wallet, $limit);
    }
}
