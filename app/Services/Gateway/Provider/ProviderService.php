<?php

namespace App\Services\Gateway\Provider;

use App\Models\Provider;
use App\Repositories\Gateway\ProviderRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class ProviderService implements ProviderServiceInterface
{
    public function __construct(private readonly ProviderRepositoryInterface $providers)
    {
    }

    public function list(): Collection
    {
        return $this->providers->list();
    }

    public function create(array $data): Provider
    {
        $config = array_filter([
            'api_key' => $data['api_key'] ?? null,
            'timeout' => $data['timeout'] ?? null,
        ], fn ($value) => ! is_null($value));

        $provider = $this->providers->create([
            'name' => $data['name'],
            'type' => $data['type'] ?? 'openai_compatible',
            'base_url' => $data['base_url'] ?? null,
            'status' => $data['status'] ?? 'active',
            'priority' => $data['priority'] ?? 0,
            'config_encrypted' => $config ?: null,
        ]);

        Cache::forget('gateway.provider.'.$provider->name);

        return $provider;
    }

    public function findOrFail(int $id): Provider
    {
        $provider = $this->providers->find($id);

        if (! $provider) {
            throw ValidationException::withMessages([
                'provider_id' => ['Provider not found.'],
            ]);
        }

        return $provider;
    }
}
