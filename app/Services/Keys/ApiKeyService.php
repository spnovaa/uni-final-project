<?php

namespace App\Services\Keys;

use App\Domains\Keys\Services\ApiKeyService as GeneratorService;
use App\Models\ApiClient;
use App\Models\ApiKey;
use App\Repositories\Keys\ApiKeyRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ApiKeyService implements ApiKeyServiceInterface
{
    public function __construct(
        private readonly ApiKeyRepositoryInterface $keys,
        private readonly GeneratorService $generator
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
        return $this->generator->create($client, $scopes, $rateLimit, $allowedIps, $expiresAt);
    }

    public function revoke(ApiKey $apiKey): ApiKey
    {
        return $this->generator->revoke($apiKey);
    }

    public function rotate(ApiKey $apiKey): array
    {
        return $this->generator->rotate($apiKey);
    }
}
