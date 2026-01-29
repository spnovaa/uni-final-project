<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use Closure;

class NormalizeProviderResponsePipe
{
    public function handle(GatewayRequestContext $context, Closure $next)
    {
        if ($context->providerResponse && is_array($context->providerResponse->body)) {
            $context->normalizedResponse = $context->providerResponse->body;
        }

        return $next($context);
    }
}
