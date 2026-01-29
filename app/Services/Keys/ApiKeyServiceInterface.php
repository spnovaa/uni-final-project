<?php

namespace App\Services\Keys;

use App\Models\ApiClient;
use App\Models\ApiKey;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

interface ApiKeyServiceInterface
{
    public function list(ApiClient $client): Collection;

    public function create(
        ApiClient $client,
        array $scopes = [],
        ?int $rateLimit = null,
        ?array $allowedIps = null,
        ?CarbonImmutable $expiresAt = null
    ): array;

    public function revoke(ApiKey $apiKey): ApiKey;

    public function rotate(ApiKey $apiKey): array;
}
