<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\DTOs\UsageMetrics;
use App\Domains\Gateway\Services\UsageMeteringService;
use Closure;

class MeterUsagePipe
{
    public function __construct(private readonly UsageMeteringService $usageService)
    {
    }

    public function handle(GatewayRequestContext $context, Closure $next)
    {
        $actual = null;
        if ($context->adapter && $context->providerResponse) {
            $actual = $context->adapter->extractUsage($context->providerResponse);
        }

        $context->usage = $this->mergeUsage($actual, $context->estimatedUsage);

        $context->usageRecords = $this->usageService->buildUsageRecords(
            $context->usage,
            $context->providerModel,
        );

        return $next($context);
    }

    private function mergeUsage(?UsageMetrics $actual, ?UsageMetrics $estimated): ?UsageMetrics
    {
        if (! $actual && ! $estimated) {
            return null;
        }

        if (! $actual) {
            return $estimated;
        }

        if (! $estimated) {
            return $actual;
        }

        return new UsageMetrics(
            $actual->promptTokens ?? $estimated->promptTokens,
            $actual->completionTokens ?? $estimated->completionTokens,
            $actual->totalTokens ?? $estimated->totalTokens,
            $actual->images ?? $estimated->images,
            $actual->audioSeconds ?? $estimated->audioSeconds,
            $actual->audioCharacters ?? $estimated->audioCharacters,
        );
    }
}
