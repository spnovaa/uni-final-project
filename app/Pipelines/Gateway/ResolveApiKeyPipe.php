<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Support\OpenAiErrorResponder;
use Closure;

class ResolveApiKeyPipe
{
    public function handle(GatewayRequestContext $context, Closure $next)
    {
        $apiKey = $context->request->attributes->get('apiKey');

        if (! $apiKey) {
            $context->normalizedResponse = OpenAiErrorResponder::authenticationError('Missing API key.')->getData(true);
            $context->status = 401;

            return $context;
        }

        $context->apiKey = $apiKey;

        return $next($context);
    }
}
