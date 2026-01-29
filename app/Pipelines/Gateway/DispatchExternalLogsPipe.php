<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Services\GatewayLogService;
use Closure;

class DispatchExternalLogsPipe
{
    public function __construct(private readonly GatewayLogService $logs)
    {
    }

    public function handle(GatewayRequestContext $context, Closure $next)
    {
        $this->logs->dispatch($context);

        return $next($context);
    }
}
