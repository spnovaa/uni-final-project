<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Support\OpenAiErrorResponder;
use Closure;

class ValidateProviderSelectionPipe
{
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
