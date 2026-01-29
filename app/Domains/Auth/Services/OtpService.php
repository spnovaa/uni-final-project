<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\DTOs\OtpContext;
use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    public function start(string $destination, string $channel, ?string $ip = null): OtpContext
    {
        $context = new OtpContext($destination, $channel, $ip);

        return app(Pipeline::class)
            ->send($context)
            ->through([
                \App\Pipelines\Auth\ThrottleOtpPipe::class,
                \App\Pipelines\Auth\GenerateOtpPipe::class,
                \App\Pipelines\Auth\DispatchOtpPipe::class,
            ])
            ->thenReturn();
    }

    public function verify(string $destination, string $code, ?string $channel = null): array
    {
        $query = OtpChallenge::query()
            ->where('destination', $destination)
            ->orderByDesc('id');

        if ($channel) {
            $query->where('channel', $channel);
        }

        $challenge = $query->first();

        if (! $challenge) {
            return [
                'ok' => false,
                'message' => 'OTP challenge not found.',
                'status' => 404,
            ];
        }

        if ($challenge->expires_at->isPast()) {
            return [
                'ok' => false,
                'message' => 'OTP has expired.',
                'status' => 400,
            ];
        }

        if ($challenge->attempts >= (int) config('otp.max_attempts', 5)) {
            return [
                'ok' => false,
                'message' => 'OTP attempts exceeded.',
                'status' => 429,
            ];
        }

        if (! Hash::check($code, $challenge->code_hash)) {
            $challenge->increment('attempts');

            return [
                'ok' => false,
                'message' => 'Invalid OTP code.',
                'status' => 400,
            ];
        }

        $user = $challenge->user_id
            ? User::query()->find($challenge->user_id)
            : $this->resolveUser($destination, $challenge->channel);

        if (! $user) {
            $user = $this->createUser($destination, $challenge->channel);
        }

        return [
            'ok' => true,
            'user' => $user,
            'status' => 200,
        ];
    }

    private function resolveUser(string $destination, string $channel): ?User
    {
        if ($channel === 'email') {
            return User::query()->where('email', $destination)->first();
        }

        if ($channel === 'sms') {
            return User::query()->where('phone', $destination)->first();
        }

        return null;
    }

    private function createUser(string $destination, string $channel): User
    {
        $data = [
            'name' => 'User',
            'status' => 'active',
            'password' => Hash::make(str()->random(16)),
        ];

        if ($channel === 'email') {
            $data['email'] = $destination;
        } else {
            $data['phone'] = $destination;
            $data['email'] = sprintf('user_%s@example.local', str()->random(10));
        }

        return User::query()->create($data);
    }
}
