<?php

namespace App\Services\Keys;

use App\Models\ApiClient;
use App\Models\ApiKey;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Service layer for api key.
 */
interface ApiKeyServiceInterface
{
    /**
     * List API keys.
     * @param ApiClient $client
     * @return Collection
     */
    public function list(ApiClient $client): Collection;

    /**
     * Create API key.
     * @param ApiClient $client
     * @param array $scopes
     * @param ?int $rateLimit
     * @param ?array $allowedIps
     * @param ?CarbonImmutable $expiresAt
     * @return array
     */
    public function create(
        ApiClient $client,
        array $scopes = [],
        ?int $rateLimit = null,
        ?array $allowedIps = null,
        ?CarbonImmutable $expiresAt = null
    ): array;

    /**
     * Revoke API key.
     * @param ApiKey $apiKey
     * @return ApiKey
     */
    public function revoke(ApiKey $apiKey): ApiKey;

    /**
     * Rotate API key and return a new secret.
     * @param ApiKey $apiKey
     * @return array
     */
    public function rotate(ApiKey $apiKey): array;
}
