<?php

namespace App\Pipelines\Auth;

use App\Domains\Auth\DTOs\OtpContext;
use App\Repositories\Auth\OtpChallengeRepositoryInterface;
use Closure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Auth pipeline step for generate otp.
 */
class GenerateOtpPipe
{
    /**
     * Create a new instance.
     * @param OtpChallengeRepositoryInterface $challenges
     * @return void
     */
    public function __construct(private readonly OtpChallengeRepositoryInterface $challenges)
    {
    }

    /**
     * Process the OTP context and continue the pipeline.
     * @param OtpContext $context
     * @param Closure $next
     * @return mixed
     */
    public function handle(OtpContext $context, Closure $next)
    {
        $length = (int) config('otp.code_length', 6);
        $code = $this->generateCode($length);
        $expiresAt = now()->addMinutes((int) config('otp.ttl_minutes', 10));

        $challenge = $this->challenges->createChallenge(
            $context->user?->id,
            $context->channel,
            $context->destination,
            Hash::make($code),
            $expiresAt,
        );

        $context->code = $code;
        $context->challenge = $challenge;

        return $next($context);
    }

    /**
     * Generate code.
     * @param int $length
     * @return string
     */
    private function generateCode(int $length): string
    {
        $digits = '';
        for ($i = 0; $i < $length; $i += 1) {
            $digits .= (string) random_int(0, 9);
        }

        return $digits;
    }
}
