<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Models\GatewayRequest;
use App\Models\UsageRecord;
use Closure;

class PersistLogsPipe
{
    public function handle(GatewayRequestContext $context, Closure $next)
    {
        if (! config('gateway.persist_logs')) {
            return $next($context);
        }

        $requestHash = null;
        if (! empty($context->payload)) {
            $requestHash = hash('sha256', json_encode($context->payload));
        }

        $gatewayRequest = GatewayRequest::query()->create([
            'api_key_id' => $context->apiKey?->id,
            'user_id' => $context->apiKey?->client?->user_id,
            'provider_id' => $context->providerId,
            'provider_model_id' => $context->providerModel?->id,
            'endpoint' => $context->endpoint,
            'request_hash' => $requestHash,
            'status' => (string) $context->status,
            'latency_ms' => $context->latencyMs(),
        ]);

        foreach ($context->usageRecords as $record) {
            UsageRecord::query()->create([
                'gateway_request_id' => $gatewayRequest->id,
                'metric' => $record['metric'],
                'quantity' => $record['quantity'],
                'unit_cost' => $record['unit_cost'],
                'total_cost' => $record['total_cost'],
            ]);
        }

        return $next($context);
    }
}
