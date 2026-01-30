<?php

namespace App\Domains\Gateway\Services;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\DTOs\ProviderResponse;
use Illuminate\Pipeline\Pipeline;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Service layer for gateway.
 */
class GatewayService
{
    /**
     * Run the OpenAI-compatible gateway pipeline.
     * @param GatewayRequestContext $context
     * @return SymfonyResponse
     */
    public function handle(GatewayRequestContext $context): SymfonyResponse
    {
        $context = app(Pipeline::class)
            ->send($context)
            ->through([
                \App\Pipelines\Gateway\ResolveApiKeyPipe::class,
                \App\Pipelines\Gateway\EnforceIpAllowlistPipe::class,
                \App\Pipelines\Gateway\ValidateGatewayPayloadPipe::class,
                \App\Pipelines\Gateway\RateLimitPipe::class,
                \App\Pipelines\Gateway\SelectProviderPipe::class,
                \App\Pipelines\Gateway\ValidateProviderSelectionPipe::class,
                \App\Pipelines\Gateway\EstimateUsagePipe::class,
                \App\Pipelines\Gateway\CheckSubscriptionOrWalletPipe::class,
                \App\Pipelines\Gateway\DispatchProviderRequestPipe::class,
                \App\Pipelines\Gateway\NormalizeProviderResponsePipe::class,
                \App\Pipelines\Gateway\MeterUsagePipe::class,
                \App\Pipelines\Gateway\PersistLogsPipe::class,
                \App\Pipelines\Gateway\ChargeUsagePipe::class,
                \App\Pipelines\Gateway\DispatchExternalLogsPipe::class,
            ])
            ->thenReturn();

        return $this->buildResponse($context);
    }

    /**
     * Build response.
     * @param GatewayRequestContext $context
     * @return SymfonyResponse
     */
    private function buildResponse(GatewayRequestContext $context): SymfonyResponse
    {
        $response = $context->providerResponse;

        if ($response instanceof ProviderResponse && $response->isBinary) {
            return response($response->body, $response->status)
                ->withHeaders($response->headers);
        }

        $body = $context->normalizedResponse;
        if (empty($body) && $response instanceof ProviderResponse && is_array($response->body)) {
            $body = $response->body;
        }

        if (empty($body)) {
            $body = [
                'error' => [
                    'message' => 'Empty response from provider.',
                    'type' => 'gateway_error',
                ],
            ];
            $context->status = $context->status ?: 502;
        }

        $status = $context->status ?: ($response->status ?? 200);

        return response()->json($body, $status);
    }
}
