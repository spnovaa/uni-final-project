<?php

namespace App\Pipelines\Auth;

use App\Domains\Auth\DTOs\OtpContext;
use Closure;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Auth pipeline step for throttle otp.
 */
class ThrottleOtpPipe
{
    /**
     * Process the OTP context and continue the pipeline.
     * @param OtpContext $context
     * @param Closure $next
     * @return mixed
     */
    public function handle(OtpContext $context, Closure $next)
    {
        $key = $this->rateLimitKey($context);
        $maxAttempts = (int) config('otp.throttle_per_minute', 5);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return $context->fail('Too many OTP requests. Please try again later.', 429);
        }

        RateLimiter::hit($key, 60);

        return $next($context);
    }

    /**
     * Rate limit key.
     * @param OtpContext $context
     * @return string
     */
    private function rateLimitKey(OtpContext $context): string
    {
        $ip = $context->ip ?: 'unknown';

        return 'otp:'.$context->channel.':'.$context->destination.':'.$ip;
    }
}
