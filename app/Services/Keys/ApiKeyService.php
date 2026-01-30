<?php

namespace App\Services\Keys;

use App\Domains\Keys\Services\ApiKeyService as GeneratorService;
use App\Models\ApiClient;
use App\Models\ApiKey;
use App\Repositories\Keys\ApiKeyRepositoryInterface;
use App\Services\Audit\AuditLogServiceInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Manage API keys used to authenticate gateway requests.
 *
 * This service delegates secret generation/hashing to the domain generator service, persists
 * key metadata via repositories, and records audit events for security tracking.
 */
class ApiKeyService implements ApiKeyServiceInterface
{
    /**
     * Create a new instance.
     * @param ApiKeyRepositoryInterface $keys
     * @param GeneratorService $generator
     * @param AuditLogServiceInterface $audit
     * @return void
     */
    public function __construct(
        private readonly ApiKeyRepositoryInterface $keys,
        private readonly GeneratorService $generator,
        private readonly AuditLogServiceInterface $audit
    ) {
    }

    /**
     * List API keys for a given client (metadata only; secrets are not returned).
     * @param ApiClient $client
     * @return Collection
     */
    public function list(ApiClient $client): Collection
    {
        return $this->keys->listByClient($client->id);
    }

    /**
     * Create a new API key and return the plaintext secret once.
     *
     * Persists only a hashed secret in the database and records an audit log entry.
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
    ): array {
        $result = $this->generator->create($client, $scopes, $rateLimit, $allowedIps, $expiresAt);

        $this->audit->record($client->user, 'api_key.created', $result['model'], [
            'client_id' => $client->id,
        ]);

        return $result;
    }

    /**
     * Revoke an API key so it can no longer authenticate requests.
     *
     * Records an audit event for traceability.
     * @param ApiKey $apiKey
     * @return ApiKey
     */
    public function revoke(ApiKey $apiKey): ApiKey
    {
        $key = $this->generator->revoke($apiKey);

        $this->audit->record($apiKey->client?->user, 'api_key.revoked', $apiKey, [
            'client_id' => $apiKey->api_client_id,
        ]);

        return $key;
    }

    /**
     * Rotate API key and return a new secret.
     *
     * Rotation invalidates the previous secret and returns a new plaintext secret once.
     * Records an audit event with both old and new key references.
     * @param ApiKey $apiKey
     * @return array
     */
    public function rotate(ApiKey $apiKey): array
    {
        $result = $this->generator->rotate($apiKey);

        $this->audit->record($apiKey->client?->user, 'api_key.rotated', $apiKey, [
            'new_key_id' => $result['model']->id,
            'client_id' => $apiKey->api_client_id,
        ]);

        return $result;
    }
}
