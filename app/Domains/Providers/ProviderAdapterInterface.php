<?php

namespace App\Domains\Providers;

use App\Domains\Gateway\DTOs\GatewayRequestDto;
use App\Domains\Gateway\DTOs\ProviderResponse;
use App\Domains\Gateway\DTOs\UsageMetrics;

/**
 * Contract for upstream provider adapters.
 *
 * Adapters translate OpenAI-compatible gateway requests into provider-specific HTTP calls and
 * normalize responses back into OpenAI-compatible shapes for the rest of the gateway pipeline.
 */
interface ProviderAdapterInterface
{
    /**
     * Determine whether the adapter supports a given endpoint/model combination.
     * @param string $endpoint
     * @param ?string $model
     * @return bool
     */
    public function supports(string $endpoint, ?string $model): bool;

    /**
     * Send the request to the upstream provider and return a raw provider response wrapper.
     * @param GatewayRequestDto $request
     * @return ProviderResponse
     */
    public function send(GatewayRequestDto $request): ProviderResponse;

    /**
     * Extract usage metrics from the provider response when the provider returns usage.
     *
     * Returns null when usage information is not available.
     * @param ProviderResponse $response
     * @return ?UsageMetrics
     */
    public function extractUsage(ProviderResponse $response): ?UsageMetrics;
}
