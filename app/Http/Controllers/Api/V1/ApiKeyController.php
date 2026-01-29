<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Keys\Services\ApiKeyService;
use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use App\Models\ApiKey;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class ApiKeyController extends Controller
{
    public function __construct(private readonly ApiKeyService $service)
    {
    }

    public function index(Request $request, ApiClient $apiClient)
    {
        $this->authorizeClient($request, $apiClient);

        $keys = $apiClient->keys()->get()->map(function (ApiKey $key) {
            return [
                'id' => $key->id,
                'key_prefix' => $key->key_prefix,
                'scopes' => $key->scopes,
                'rate_limit_per_min' => $key->rate_limit_per_min,
                'allowed_ips' => $key->allowed_ips,
                'expires_at' => $key->expires_at,
                'revoked_at' => $key->revoked_at,
                'created_at' => $key->created_at,
            ];
        });

        return response()->json($keys);
    }

    public function store(Request $request, ApiClient $apiClient)
    {
        $this->authorizeClient($request, $apiClient);

        $data = $request->validate([
            'scopes' => ['nullable', 'array'],
            'rate_limit_per_min' => ['nullable', 'integer', 'min:1'],
            'allowed_ips' => ['nullable', 'array'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $expiresAt = isset($data['expires_at'])
            ? CarbonImmutable::parse($data['expires_at'])
            : null;

        $result = $this->service->create(
            $apiClient,
            $data['scopes'] ?? [],
            $data['rate_limit_per_min'] ?? null,
            $data['allowed_ips'] ?? null,
            $expiresAt,
        );

        return response()->json([
            'api_key' => $result['api_key'],
            'key_prefix' => $result['model']->key_prefix,
            'expires_at' => $result['model']->expires_at,
        ], 201);
    }

    public function revoke(Request $request, ApiKey $apiKey)
    {
        $this->authorizeKey($request, $apiKey);

        $this->service->revoke($apiKey);

        return response()->json([
            'status' => 'revoked',
        ]);
    }

    public function rotate(Request $request, ApiKey $apiKey)
    {
        $this->authorizeKey($request, $apiKey);

        $result = $this->service->rotate($apiKey);

        return response()->json([
            'api_key' => $result['api_key'],
            'key_prefix' => $result['model']->key_prefix,
            'expires_at' => $result['model']->expires_at,
        ]);
    }

    private function authorizeClient(Request $request, ApiClient $apiClient): void
    {
        if ((int) $apiClient->user_id !== (int) $request->user()->id) {
            abort(404);
        }
    }

    private function authorizeKey(Request $request, ApiKey $apiKey): void
    {
        if ((int) ($apiKey->client?->user_id ?? 0) !== (int) $request->user()->id) {
            abort(404);
        }
    }
}
