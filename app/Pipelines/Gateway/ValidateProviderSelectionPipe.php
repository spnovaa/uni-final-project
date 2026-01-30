<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Support\OpenAiErrorResponder;
use Closure;

/**
 * Gateway pipeline step for validate provider selection.
 */
class ValidateProviderSelectionPipe
{
    /**
     * Process the gateway context and continue the pipeline.
     * @param GatewayRequestContext $context
     * @param Closure $next
     * @return mixed
     */
    public function handle(GatewayRequestContext $context, Closure $next)
    {
        if (! $context->providerName || ! $context->modelKey) {
            $context->normalizedResponse = OpenAiErrorResponder::invalidRequest(
                'Provider and model are required.',
                'provider'
            )->getData(true);
            $context->status = 400;

            return $context;
        }

        if (! $context->providerModel) {
            $context->normalizedResponse = OpenAiErrorResponder::invalidRequest(
                'Unknown provider or model.',
                'model'
            )->getData(true);
            $context->status = 400;

            return $context;
        }

        return $next($context);
    }
}
