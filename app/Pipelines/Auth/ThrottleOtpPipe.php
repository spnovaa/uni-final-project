<?php

namespace App\Pipelines\Auth;

use App\Domains\Auth\DTOs\OtpContext;
use Closure;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Throttle OTP challenge creation to prevent abuse.
 *
 * Uses Laravel RateLimiter keyed by channel + destination + caller IP. On too many attempts,
 * this pipe sets a 429 error on the context and stops the pipeline.
 */
class ThrottleOtpPipe
{
    /**
     * Apply per-minute throttling for OTP challenge creation.
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
     * Build a rate limit key for an OTP request.
     * @param OtpContext $context
     * @return string
     */
    private function rateLimitKey(OtpContext $context): string
    {
        $ip = $context->ip ?: 'unknown';

        return 'otp:'.$context->channel.':'.$context->destination.':'.$ip;
    }
}
