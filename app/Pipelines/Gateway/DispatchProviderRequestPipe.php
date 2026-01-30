<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\DTOs\GatewayRequestDto;
use App\Domains\Gateway\Support\OpenAiErrorResponder;
use App\Domains\Providers\ProviderAdapterResolver;
use Closure;
use Illuminate\Support\Arr;
use RuntimeException;
use Throwable;

/**
 * Dispatch the gateway request to the selected upstream provider via an adapter.
 *
 * This pipe resolves the correct provider adapter, builds a request DTO from the incoming
 * OpenAI-compatible payload, performs the HTTP request, and stores the raw provider response
 * on the context for later normalization and billing.
 */
class DispatchProviderRequestPipe
{
    /**
     * Create a new instance.
     * @param ProviderAdapterResolver $resolver
     * @return void
     */
    public function __construct(private readonly ProviderAdapterResolver $resolver)
    {
    }

    /**
     * Resolve the provider adapter, send the request, and capture the provider response.
     *
     * - RuntimeException: provider does not support the requested endpoint (400).
     * - Other exceptions: treated as upstream provider failures (502).
     * @param GatewayRequestContext $context
     * @param Closure $next
     * @return mixed
     */
    public function handle(GatewayRequestContext $context, Closure $next)
    {
        try {
            $adapter = $this->resolver->resolve($context->providerName, $context->endpoint, $context->modelKey);
            $context->adapter = $adapter;

            $payload = Arr::except($context->payload, ['provider']);

            $dto = new GatewayRequestDto(
                $context->endpoint,
                $payload,
                $context->files,
                $context->providerName,
                $context->modelKey,
                $context->providerConfig,
            );

            $context->providerResponse = $adapter->send($dto);
            $context->status = $context->providerResponse->status;
        } catch (RuntimeException $exception) {
            $context->normalizedResponse = OpenAiErrorResponder::invalidRequest(
                'Provider does not support this endpoint.',
                'provider'
            )->getData(true);
            $context->status = 400;

            return $context;
        } catch (Throwable $exception) {
            $context->normalizedResponse = OpenAiErrorResponder::serverError('Upstream provider error.')->getData(true);
            $context->status = 502;

            return $context;
        }

        return $next($context);
    }
}
