<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Support\OpenAiErrorResponder;
use Closure;

/**
 * Validate that the request explicitly selected a provider and a model.
 *
 * The project requirement is that every gateway request includes `provider` and `model`.
 * This pipe enforces that rule and also ensures the provider/model exists in the database.
 */
class ValidateProviderSelectionPipe
{
    /**
     * Ensure provider + model selection is present and resolvable.
     *
     * On failure, sets an OpenAI-compatible `invalid_request_error` and stops the pipeline.
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
