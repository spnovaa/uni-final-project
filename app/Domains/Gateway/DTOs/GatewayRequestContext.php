<?php

namespace App\Domains\Gateway\DTOs;

use App\Models\ApiKey;
use App\Models\ProviderModel;
use App\Models\Subscription;
use App\Domains\Providers\ProviderAdapterInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * DTO for gateway request context.
 */
class GatewayRequestContext
{
    public string $requestId;
    public float $startTime;
    public ?ApiKey $apiKey = null;
    public ?string $providerName = null;
    public ?array $providerConfig = null;
    public ?int $providerId = null;
    public ?string $modelKey = null;
    public ?ProviderModel $providerModel = null;
    public ?ProviderAdapterInterface $adapter = null;
    public ?ProviderResponse $providerResponse = null;
    public array $normalizedResponse = [];
    public ?UsageMetrics $usage = null;
    public array $usageRecords = [];
    public int $status = 200;
    public ?Subscription $subscription = null;
    public ?UsageMetrics $estimatedUsage = null;
    public array $estimatedUsageRecords = [];
    public float $estimatedTotalCost = 0.0;

    /**
     * Create a new instance.
     *
     * Initializes a unique request ID, captures the start time for latency tracking, and extracts
     * `provider` and `model` from the payload for routing.
     * @param Request $request
     * @param string $endpoint
     * @param array $payload
     * @param array $files
     * @return void
     */
    public function __construct(
        public Request $request,
        public string $endpoint,
        public array $payload,
        public array $files = [],
    ) {
        $this->requestId = (string) Str::uuid();
        $this->startTime = microtime(true);
        $this->providerName = $payload['provider'] ?? null;
        $this->modelKey = $payload['model'] ?? null;
    }

    /**
     * Compute elapsed time in milliseconds since the context was created.
     * @return int
     */
    public function latencyMs(): int
    {
        return (int) round((microtime(true) - $this->startTime) * 1000);
    }
}
