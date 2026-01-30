<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\DTOs\UsageMetrics;
use App\Domains\Gateway\Services\UsageMeteringService;
use Closure;

/**
 * Convert provider response usage information into billable usage records.
 *
 * This pipe attempts to extract real usage from the upstream provider response (when the
 * adapter supports it). When the provider does not return usage, it falls back to the
 * tokenizer-based estimate computed earlier in the pipeline.
 */
class MeterUsagePipe
{
    /**
     * Create a new instance.
     * @param UsageMeteringService $usageService
     * @return void
     */
    public function __construct(private readonly UsageMeteringService $usageService)
    {
    }

    /**
     * Extract actual usage (if available), merge it with estimates, and build usage records.
     * @param GatewayRequestContext $context
     * @param Closure $next
     * @return mixed
     */
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

    /**
     * Merge actual and estimated usage metrics, preferring actual values when present.
     * @param ?UsageMetrics $actual
     * @param ?UsageMetrics $estimated
     * @return ?UsageMetrics
     */
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
