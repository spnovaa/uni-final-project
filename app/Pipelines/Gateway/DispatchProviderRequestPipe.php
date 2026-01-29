<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\DTOs\GatewayRequestDto;
use App\Domains\Gateway\Support\OpenAiErrorResponder;
use App\Domains\Providers\ProviderAdapterResolver;
use Closure;
use Throwable;

class DispatchProviderRequestPipe
{
    public function __construct(private readonly ProviderAdapterResolver $resolver)
    {
    }

    public function handle(GatewayRequestContext $context, Closure $next)
    {
        try {
            $adapter = $this->resolver->resolve($context->providerName, $context->endpoint, $context->modelKey);
            $context->adapter = $adapter;

            $dto = new GatewayRequestDto(
                $context->endpoint,
                $context->payload,
                $context->files,
                $context->providerName,
                $context->modelKey,
                $context->providerConfig,
            );

            $context->providerResponse = $adapter->send($dto);
            $context->status = $context->providerResponse->status;
        } catch (Throwable $exception) {
            $context->normalizedResponse = OpenAiErrorResponder::serverError('Upstream provider error.')->getData(true);
            $context->status = 502;

            return $context;
        }

        return $next($context);
    }
}
