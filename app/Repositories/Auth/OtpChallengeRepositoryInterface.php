<?php

namespace App\Repositories\Auth;

use App\Models\OtpChallenge;
use Carbon\CarbonInterface;

/**
 * Persistence layer for otp challenge.
 */
interface OtpChallengeRepositoryInterface
{
    /**
     * Create challenge.
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
     * Latest for destination.
     * @param string $destination
     * @param ?string $channel
     * @return ?OtpChallenge
     */
    public function latestForDestination(string $destination, ?string $channel = null): ?OtpChallenge;

    /**
     * Increment attempts.
     * @param OtpChallenge $challenge
     * @return OtpChallenge
     */
    public function incrementAttempts(OtpChallenge $challenge): OtpChallenge;
}
