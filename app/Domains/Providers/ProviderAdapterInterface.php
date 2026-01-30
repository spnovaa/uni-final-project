<?php

namespace App\Domains\Providers;

use App\Domains\Gateway\DTOs\GatewayRequestDto;
use App\Domains\Gateway\DTOs\ProviderResponse;
use App\Domains\Gateway\DTOs\UsageMetrics;

/**
 * Interface ProviderAdapterInterface.
 */
interface ProviderAdapterInterface
{
    /**
     * Supports.
     * @param string $endpoint
     * @param ?string $model
     * @return bool
     */
    public function supports(string $endpoint, ?string $model): bool;

    /**
     * Send.
     * @param GatewayRequestDto $request
     * @return ProviderResponse
     */
    public function send(GatewayRequestDto $request): ProviderResponse;

    /**
     * Extract usage.
     * @param ProviderResponse $response
     * @return ?UsageMetrics
     */
    public function extractUsage(ProviderResponse $response): ?UsageMetrics;
}
