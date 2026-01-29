<?php

namespace App\Domains\Providers;

use App\Domains\Gateway\DTOs\GatewayRequestDto;
use App\Domains\Gateway\DTOs\ProviderResponse;
use App\Domains\Gateway\DTOs\UsageMetrics;

interface ProviderAdapterInterface
{
    public function supports(string $endpoint, ?string $model): bool;

    public function send(GatewayRequestDto $request): ProviderResponse;

    public function extractUsage(ProviderResponse $response): ?UsageMetrics;
}
