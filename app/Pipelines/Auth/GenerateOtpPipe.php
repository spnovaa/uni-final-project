<?php

namespace App\Pipelines\Auth;

use App\Domains\Auth\DTOs\OtpContext;
use App\Repositories\Auth\OtpChallengeRepositoryInterface;
use Closure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Generate and persist a new OTP challenge.
 *
 * This pipe generates a random numeric code, stores a hashed version in the database with an
 * expiration time, and attaches the plaintext code to the context so it can be delivered to
 * the user by a later pipeline step.
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
     * Create a new OTP challenge record and attach it to the context.
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
     * Generate a random numeric OTP code of a given length.
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
