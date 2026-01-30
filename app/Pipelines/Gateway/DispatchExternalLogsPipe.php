<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Services\GatewayLogService;
use Closure;

/**
 * Dispatch request/usage logs to an external log sink asynchronously.
 *
 * The gateway persists minimal DB logs for reporting, but external log shipping is queued
 * so the request latency does not depend on third-party logging services.
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
     * Queue an external log dispatch job for this gateway request.
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
