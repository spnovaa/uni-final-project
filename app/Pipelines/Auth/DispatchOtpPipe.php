<?php

namespace App\Pipelines\Auth;

use App\Domains\Auth\DTOs\OtpContext;
use App\Jobs\SendOtpJob;
use Closure;

/**
 * Auth pipeline step for dispatch otp.
 */
class DispatchOtpPipe
{
    /**
     * Process the OTP context and continue the pipeline.
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
