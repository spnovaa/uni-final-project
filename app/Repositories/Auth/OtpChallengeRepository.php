<?php

namespace App\Repositories\Auth;

use App\Models\OtpChallenge;
use Carbon\CarbonInterface;

class OtpChallengeRepository implements OtpChallengeRepositoryInterface
{
    public function createChallenge(
        ?int $userId,
        string $channel,
        string $destination,
        string $codeHash,
        CarbonInterface $expiresAt
    ): OtpChallenge {
        return OtpChallenge::query()->create([
            'user_id' => $userId,
            'channel' => $channel,
            'destination' => $destination,
            'code_hash' => $codeHash,
            'expires_at' => $expiresAt,
            'attempts' => 0,
        ]);
    }

    public function latestForDestination(string $destination, ?string $channel = null): ?OtpChallenge
    {
        $query = OtpChallenge::query()
            ->where('destination', $destination)
            ->orderByDesc('id');

        if ($channel) {
            $query->where('channel', $channel);
        }

        return $query->first();
    }

    public function incrementAttempts(OtpChallenge $challenge): OtpChallenge
    {
        $challenge->increment('attempts');

        return $challenge->refresh();
    }
}
