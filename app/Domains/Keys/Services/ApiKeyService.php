<?php

namespace App\Domains\Keys\Services;

use App\Models\ApiClient;
use App\Models\ApiKey;
use App\Repositories\Keys\ApiKeyRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Service layer for api key.
 */
class ApiKeyService
{
    /**
     * Create a new instance.
     * @param ApiKeyRepositoryInterface $keys
     * @return void
     */
    public function __construct(private readonly ApiKeyRepositoryInterface $keys)
    {
    }

    /**
     * Create.
     * @param ApiClient $client
     * @param array $scopes
     * @param ?int $rateLimit
     * @param ?array $allowedIps
     * @param ?CarbonImmutable $expiresAt
     * @return array
     */
    public function create(ApiClient $client, array $scopes = [], ?int $rateLimit = null, ?array $allowedIps = null, ?CarbonImmutable $expiresAt = null): array
    {
        $rawKey = $this->generateRawKey();
        $prefix = $this->extractPrefix($rawKey);

        $apiKey = $this->keys->create([
            'api_client_id' => $client->id,
            'key_prefix' => $prefix,
            'key_hash' => $this->hashToken($rawKey),
            'scopes' => $scopes ?: null,
            'rate_limit_per_min' => $rateLimit,
            'allowed_ips' => $allowedIps ?: null,
            'expires_at' => $expiresAt,
        ]);

        return [
            'api_key' => $rawKey,
            'model' => $apiKey,
        ];
    }

    /**
     * Revoke.
     * @param ApiKey $apiKey
     * @return ApiKey
     */
    public function revoke(ApiKey $apiKey): ApiKey
    {
        $apiKey->forceFill([
            'revoked_at' => now(),
        ]);
        $this->keys->save($apiKey);

        return $apiKey;
    }

    /**
     * Rotate.
     * @param ApiKey $apiKey
     * @return array
     */
    public function rotate(ApiKey $apiKey): array
    {
        $this->revoke($apiKey);

        return $this->create(
            $apiKey->client,
            $apiKey->scopes ?? [],
            $apiKey->rate_limit_per_min,
            $apiKey->allowed_ips ?? [],
            $apiKey->expires_at ? CarbonImmutable::parse($apiKey->expires_at) : null
        );
    }

    /**
     * Generate raw key.
     * @return string
     */
    private function generateRawKey(): string
    {
        return 'gk_'.Str::random(40);
    }

    /**
     * Extract prefix.
     * @param string $rawKey
     * @return string
     */
    private function extractPrefix(string $rawKey): string
    {
        $token = Str::startsWith($rawKey, 'gk_') ? substr($rawKey, 3) : $rawKey;

        return substr($token, 0, 8);
    }

    /**
     * Hash token.
     * @param string $token
     * @return string
     */
    private function hashToken(string $token): string
    {
        return hash_hmac('sha256', $token, config('app.key'));
    }
}
