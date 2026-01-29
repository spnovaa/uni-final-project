<?php

namespace App\Pipelines\Auth;

use App\Domains\Auth\DTOs\OtpContext;
use App\Jobs\SendOtpJob;
use Closure;

class DispatchOtpPipe
{
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
