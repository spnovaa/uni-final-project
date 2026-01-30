<?php

namespace App\Repositories\Auth;

use App\Models\OtpChallenge;
use Carbon\CarbonInterface;

/**
 * Repository contract for querying and persisting OTP challenge records.
 */
interface OtpChallengeRepositoryInterface
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
    ): OtpChallenge;

    /**
     * Get the most recent OTP challenge for a destination (and optional channel).
     * @param string $destination
     * @param ?string $channel
     * @return ?OtpChallenge
     */
    public function latestForDestination(string $destination, ?string $channel = null): ?OtpChallenge;

    /**
     * Increment the attempts counter for a challenge.
     * @param OtpChallenge $challenge
     * @return OtpChallenge
     */
    public function incrementAttempts(OtpChallenge $challenge): OtpChallenge;
}
