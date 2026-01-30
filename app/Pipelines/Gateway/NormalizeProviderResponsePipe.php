<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use Closure;

/**
 * Normalize the provider response into an OpenAI-compatible JSON payload.
 *
 * Provider adapters may already return OpenAI-shaped arrays. This pipe copies the raw
 * provider response body into `normalizedResponse` so downstream steps can meter usage,
 * persist logs, and build the final HTTP response consistently.
 */
class NormalizeProviderResponsePipe
{
    /**
     * Set `normalizedResponse` when the provider response is JSON-like.
     * @param GatewayRequestContext $context
     * @param Closure $next
     * @return mixed
     */
    public function handle(GatewayRequestContext $context, Closure $next)
    {
        if ($context->providerResponse && is_array($context->providerResponse->body)) {
            $context->normalizedResponse = $context->providerResponse->body;
        }

        return $next($context);
    }
}
