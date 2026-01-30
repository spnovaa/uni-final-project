<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Support\OpenAiErrorResponder;
use Closure;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Enforce per-API-key rate limiting for gateway requests.
 *
 * Uses Laravel's RateLimiter with a key derived from the API key ID and returns an
 * OpenAI-style `rate_limit_error` when the configured per-minute limit is exceeded.
 */
class RateLimitPipe
{
    /**
     * Apply rate limiting and short-circuit with a 429 response when exceeded.
     * @param GatewayRequestContext $context
     * @param Closure $next
     * @return mixed
     */
    public function handle(GatewayRequestContext $context, Closure $next)
    {
        $apiKey = $context->apiKey;

        if (! $apiKey) {
            return $next($context);
        }

        $limit = $apiKey->rate_limit_per_min ?? config('gateway.rate_limit_per_min', 60);

        if ($limit <= 0) {
            return $next($context);
        }

        $key = 'gateway:api_key:'.$apiKey->id;

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $context->normalizedResponse = OpenAiErrorResponder::rateLimitError('Rate limit exceeded.')->getData(true);
            $context->status = 429;

            return $context;
        }

        RateLimiter::hit($key, 60);

        return $next($context);
    }
}
