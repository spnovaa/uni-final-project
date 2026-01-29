<?php

namespace App\Repositories\Auth;

use App\Models\OtpChallenge;
use Carbon\CarbonInterface;

interface OtpChallengeRepositoryInterface
{
    public function createChallenge(
        ?int $userId,
        string $channel,
        string $destination,
        string $codeHash,
        CarbonInterface $expiresAt
    ): OtpChallenge;

    public function latestForDestination(string $destination, ?string $channel = null): ?OtpChallenge;

    public function incrementAttempts(OtpChallenge $challenge): OtpChallenge;
}
