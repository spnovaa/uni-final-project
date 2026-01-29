<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Services\UsageEstimationService;
use App\Domains\Gateway\Services\UsageMeteringService;
use Closure;

class EstimateUsagePipe
{
    public function __construct(
        private readonly UsageEstimationService $estimator,
        private readonly UsageMeteringService $metering
    ) {
    }

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
