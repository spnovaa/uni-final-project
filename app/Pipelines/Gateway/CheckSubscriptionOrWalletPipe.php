<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Support\OpenAiErrorResponder;
use App\Repositories\Billing\SubscriptionRepositoryInterface;
use App\Repositories\Billing\WalletRepositoryInterface;
use Closure;

class CheckSubscriptionOrWalletPipe
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptions,
        private readonly WalletRepositoryInterface $wallets
    ) {
    }

    public function handle(GatewayRequestContext $context, Closure $next)
    {
        if (! config('gateway.require_wallet', true)) {
            return $next($context);
        }

        $userId = $context->apiKey?->client?->user_id;

        if (! $userId) {
            $context->normalizedResponse = OpenAiErrorResponder::authenticationError('User not found for API key.')->getData(true);
            $context->status = 401;

            return $context;
        }

        $subscription = $this->subscriptions->currentForUser($userId);
        if ($subscription && $subscription->status === 'active' && $subscription->ends_at?->isFuture()) {
            $context->subscription = $subscription;

            $included = (float) ($subscription->plan?->included_credits ?? 0);
            if ($included > 0) {
                return $next($context);
            }
        }

        $wallet = $this->wallets->findByUserId($userId);

        if (! $wallet) {
            $context->normalizedResponse = OpenAiErrorResponder::paymentRequired('Insufficient wallet balance.')->getData(true);
            $context->status = 402;

            return $context;
        }

        $balance = (float) $wallet->balance;

        if ($context->estimatedTotalCost > 0 && $balance < $context->estimatedTotalCost) {
            $context->normalizedResponse = OpenAiErrorResponder::paymentRequired('Insufficient wallet balance.')->getData(true);
            $context->status = 402;

            return $context;
        }

        if ($balance <= 0) {
            $context->normalizedResponse = OpenAiErrorResponder::paymentRequired('Insufficient wallet balance.')->getData(true);
            $context->status = 402;

            return $context;
        }

        return $next($context);
    }
}
