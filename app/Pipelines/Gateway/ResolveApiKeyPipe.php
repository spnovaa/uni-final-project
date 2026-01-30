<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Support\OpenAiErrorResponder;
use Closure;

/**
 * Gateway pipeline step for resolve api key.
 */
class ResolveApiKeyPipe
{
    /**
     * Process the gateway context and continue the pipeline.
     * @param GatewayRequestContext $context
     * @param Closure $next
     * @return mixed
     */
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
