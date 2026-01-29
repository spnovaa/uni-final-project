<?php

namespace App\Pipelines\Auth;

use App\Domains\Auth\DTOs\OtpContext;
use App\Repositories\Auth\OtpChallengeRepositoryInterface;
use Closure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GenerateOtpPipe
{
    public function __construct(private readonly OtpChallengeRepositoryInterface $challenges)
    {
    }

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

    private function generateCode(int $length): string
    {
        $digits = '';
        for ($i = 0; $i < $length; $i += 1) {
            $digits .= (string) random_int(0, 9);
        }

        return $digits;
    }
}
