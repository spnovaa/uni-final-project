<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Support\OpenAiErrorResponder;
use App\Services\Billing\Wallet\WalletServiceInterface;
use Closure;
use Illuminate\Validation\ValidationException;

class ChargeUsagePipe
{
    public function __construct(private readonly WalletServiceInterface $wallets)
    {
    }

    public function handle(GatewayRequestContext $context, Closure $next)
    {
        $user = $context->apiKey?->client?->user;

        if (! $user) {
            return $next($context);
        }

        $totalCost = collect($context->usageRecords)->sum('total_cost');

        if ($totalCost <= 0) {
            return $next($context);
        }

        $subscription = $context->subscription;
        if ($subscription && $subscription->status === 'active') {
            $included = (float) ($subscription->plan?->included_credits ?? 0);
            if ($included > 0) {
                return $next($context);
            }
        }

        try {
            $this->wallets->debit($user, (float) $totalCost, 'usage', [
                'request_id' => $context->requestId,
                'endpoint' => $context->endpoint,
                'provider' => $context->providerName,
                'model' => $context->modelKey,
            ]);
        } catch (ValidationException $exception) {
            $context->normalizedResponse = OpenAiErrorResponder::paymentRequired('Insufficient wallet balance.')->getData(true);
            $context->status = 402;

            return $context;
        }

        return $next($context);
    }
}
