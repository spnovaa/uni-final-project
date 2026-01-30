<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\DTOs\OtpContext;
use App\Models\User;
use App\Repositories\Auth\OtpChallengeRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Auth\OtpServiceInterface;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Hash;

/**
 * Service layer for otp.
 */
class OtpService implements OtpServiceInterface
{
    /**
     * Create a new instance.
     * @param OtpChallengeRepositoryInterface $challenges
     * @param UserRepositoryInterface $users
     * @return void
     */
    public function __construct(
        private readonly OtpChallengeRepositoryInterface $challenges,
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * Start.
     * @param string $destination
     * @param string $channel
     * @param ?string $ip
     * @return OtpContext
     */
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

    /**
     * Verify.
     * @param string $destination
     * @param string $code
     * @param ?string $channel
     * @return array
     */
    public function verify(string $destination, string $code, ?string $channel = null): array
    {
        $challenge = $this->challenges->latestForDestination($destination, $channel);

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
            $this->challenges->incrementAttempts($challenge);

            return [
                'ok' => false,
                'message' => 'Invalid OTP code.',
                'status' => 400,
            ];
        }

        $user = $challenge->user_id
            ? $this->users->find($challenge->user_id)
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

    /**
     * Resolve user.
     * @param string $destination
     * @param string $channel
     * @return ?User
     */
    private function resolveUser(string $destination, string $channel): ?User
    {
        if ($channel === 'email') {
            return $this->users->findByEmail($destination);
        }

        if ($channel === 'sms') {
            return $this->users->findByPhone($destination);
        }

        return null;
    }

    /**
     * Create user.
     * @param string $destination
     * @param string $channel
     * @return User
     */
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

        return $this->users->create($data);
    }
}
