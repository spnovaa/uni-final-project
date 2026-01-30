<?php

namespace App\Repositories\Auth;

use App\Models\OtpChallenge;
use Carbon\CarbonInterface;

/**
 * Repository for querying and persisting OTP challenge records.
 */
class OtpChallengeRepository implements OtpChallengeRepositoryInterface
{
    /**
     * Create a new OTP challenge record with a hashed code and expiry.
     * @param ?int $userId
     * @param string $channel
     * @param string $destination
     * @param string $codeHash
     * @param CarbonInterface $expiresAt
     * @return OtpChallenge
     */
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

    /**
     * Get the most recent OTP challenge for a destination (and optional channel).
     * @param string $destination
     * @param ?string $channel
     * @return ?OtpChallenge
     */
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

    /**
     * Increment the attempts counter for a challenge and return the refreshed model.
     * @param OtpChallenge $challenge
     * @return OtpChallenge
     */
    public function incrementAttempts(OtpChallenge $challenge): OtpChallenge
    {
        $challenge->increment('attempts');

        return $challenge->refresh();
    }
}
