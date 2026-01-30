<?php

namespace App\Services\Keys;

use App\Models\ApiClient;
use App\Models\ApiKey;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * API key service contract.
 *
 * API keys authenticate gateway requests and are stored hashed. Implementations may record
 * audit events and enforce additional policies.
 */
interface ApiKeyServiceInterface
{
    /**
     * List API keys for a client (metadata only).
     * @param ApiClient $client
     * @return Collection
     */
    public function list(ApiClient $client): Collection;

    /**
     * Create a new API key and return the plaintext secret once.
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
     * Revoke an API key so it can no longer authenticate requests.
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
