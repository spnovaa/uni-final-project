<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Services\UsageEstimationService;
use App\Domains\Gateway\Services\UsageMeteringService;
use Closure;

/**
 * Estimate usage and cost for the request before sending it to the provider.
 *
 * This pipe uses the tokenizer-based estimator to approximate token/non-token usage,
 * then converts those metrics into billable usage records using provider model pricing.
 */
class EstimateUsagePipe
{
    /**
     * Create a new instance.
     * @param UsageEstimationService $estimator
     * @param UsageMeteringService $metering
     * @return void
     */
    public function __construct(
        private readonly UsageEstimationService $estimator,
        private readonly UsageMeteringService $metering
    ) {
    }

    /**
     * Compute estimated usage metrics and the estimated total cost for pre-checks.
     * @param GatewayRequestContext $context
     * @param Closure $next
     * @return mixed
     */
    public function handle(GatewayRequestContext $context, Closure $next)
    {
        $context->estimatedUsage = $this->estimator->estimate($context);
        $context->estimatedUsageRecords = $this->metering->buildUsageRecords(
            $context->estimatedUsage,
            $context->providerModel
        );
        $context->estimatedTotalCost = (float) collect($context->estimatedUsageRecords)->sum('total_cost');

        return $next($context);
    }
}
