<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Services\UsageMeteringService;
use Closure;

class MeterUsagePipe
{
    public function __construct(private readonly UsageMeteringService $usageService)
    {
    }

    public function handle(GatewayRequestContext $context, Closure $next)
    {
        if ($context->adapter && $context->providerResponse) {
            $context->usage = $context->adapter->extractUsage($context->providerResponse);
        }

        $context->usageRecords = $this->usageService->buildUsageRecords(
            $context->usage,
            $context->providerModel,
        );

        return $next($context);
    }
}
