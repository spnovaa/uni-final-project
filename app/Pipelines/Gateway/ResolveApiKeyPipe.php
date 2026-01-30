<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Support\OpenAiErrorResponder;
use Closure;

/**
 * Resolve the authenticated API key and attach it to the gateway context.
 *
 * This pipe expects `AuthenticateApiKey` middleware to have already set the API key
 * on the request attributes. If it is missing, the pipe short-circuits the pipeline
 * with an OpenAI-style authentication error.
 */
class ResolveApiKeyPipe
{
    /**
     * Read the API key from the request and store it on the context.
     *
     * On missing key, sets an OpenAI-compatible error response and stops the pipeline.
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
