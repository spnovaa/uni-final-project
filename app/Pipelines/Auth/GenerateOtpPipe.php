<?php

namespace App\Pipelines\Auth;

use App\Domains\Auth\DTOs\OtpContext;
use App\Models\OtpChallenge;
use Closure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GenerateOtpPipe
{
    public function handle(OtpContext $context, Closure $next)
    {
        $length = (int) config('otp.code_length', 6);
        $code = $this->generateCode($length);
        $expiresAt = now()->addMinutes((int) config('otp.ttl_minutes', 10));

        $challenge = OtpChallenge::query()->create([
            'user_id' => $context->user?->id,
            'channel' => $context->channel,
            'destination' => $context->destination,
            'code_hash' => Hash::make($code),
            'expires_at' => $expiresAt,
            'attempts' => 0,
        ]);

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
