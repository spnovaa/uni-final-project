<?php

namespace App\Services\Keys;

use App\Domains\Keys\Services\ApiKeyService as GeneratorService;
use App\Models\ApiClient;
use App\Models\ApiKey;
use App\Repositories\Keys\ApiKeyRepositoryInterface;
use App\Services\Audit\AuditLogServiceInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ApiKeyService implements ApiKeyServiceInterface
{
    public function __construct(
        private readonly ApiKeyRepositoryInterface $keys,
        private readonly GeneratorService $generator,
        private readonly AuditLogServiceInterface $audit
    ) {
    }

    public function list(ApiClient $client): Collection
    {
        return $this->keys->listByClient($client->id);
    }

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

    public function revoke(ApiKey $apiKey): ApiKey
    {
        $key = $this->generator->revoke($apiKey);

        $this->audit->record($apiKey->client?->user, 'api_key.revoked', $apiKey, [
            'client_id' => $apiKey->api_client_id,
        ]);

        return $key;
    }

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
