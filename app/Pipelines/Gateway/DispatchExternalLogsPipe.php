<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Services\GatewayLogService;
use Closure;

/**
 * Gateway pipeline step for dispatch external logs.
 */
class DispatchExternalLogsPipe
{
    /**
     * Create a new instance.
     * @param GatewayLogService $logs
     * @return void
     */
    public function __construct(private readonly GatewayLogService $logs)
    {
    }

    /**
     * Process the gateway context and continue the pipeline.
     * @param GatewayRequestContext $context
     * @param Closure $next
     * @return mixed
     */
    public function handle(GatewayRequestContext $context, Closure $next)
    {
        $this->logs->dispatch($context);

        return $next($context);
    }
}
