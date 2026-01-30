<?php

namespace App\Pipelines\Auth;

use App\Domains\Auth\DTOs\OtpContext;
use App\Jobs\SendOtpJob;
use Closure;

/**
 * Queue OTP delivery after a challenge has been created.
 *
 * This pipe dispatches a `SendOtpJob` so OTP sending is asynchronous and does not block
 * the HTTP request that starts the OTP flow.
 */
class DispatchOtpPipe
{
    /**
     * Dispatch the OTP sending job when a code is available on the context.
     * @param OtpContext $context
     * @param Closure $next
     * @return mixed
     */
    public function handle(OtpContext $context, Closure $next)
    {
        if ($context->code) {
            SendOtpJob::dispatch(
                $context->destination,
                $context->channel,
                $context->code,
            );
        }

        return $next($context);
    }
}
