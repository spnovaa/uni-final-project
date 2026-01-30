<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use Closure;

/**
 * Gateway pipeline step for normalize provider response.
 */
class NormalizeProviderResponsePipe
{
    /**
     * Process the gateway context and continue the pipeline.
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
