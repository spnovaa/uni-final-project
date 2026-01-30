<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Keys\ApiKeyResource;
use App\Models\ApiClient;
use App\Models\ApiKey;
use App\Services\Keys\ApiKeyServiceInterface;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

/**
 * API controller for api key endpoints.
 */
class ApiKeyController extends Controller
{
    /**
     * Create a new instance.
     * @param ApiKeyServiceInterface $service
     * @return void
     */
    public function __construct(private readonly ApiKeyServiceInterface $service)
    {
    }

    /**
     * List API keys for a given API client owned by the authenticated user.
     *
     * The API key secret is never returned here; only metadata is exposed.
     * @param Request $request
     * @param ApiClient $apiClient
     * @return mixed
     */
    public function index(Request $request, ApiClient $apiClient)
    {
        $this->authorizeClient($request, $apiClient);

        $keys = $this->service->list($apiClient);

        return response()->json(ApiKeyResource::collection($keys));
    }

    /**
     * Create a new API key for the given client and return the plaintext secret once.
     *
     * Validates optional settings (scopes, rate limit, IP allowlist, expiry) and returns
     * both the created key metadata and the generated secret value.
     * @param Request $request
     * @param ApiClient $apiClient
     * @return mixed
     */
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
            'resource' => ApiKeyResource::make($result['model']),
        ], 201);
    }

    /**
     * Revoke an API key owned by the authenticated user.
     *
     * Revoked keys can no longer authenticate gateway requests.
     * @param Request $request
     * @param ApiKey $apiKey
     * @return mixed
     */
    public function revoke(Request $request, ApiKey $apiKey)
    {
        $this->authorizeKey($request, $apiKey);

        $this->service->revoke($apiKey);

        return response()->json([
            'status' => 'revoked',
        ]);
    }

    /**
     * Rotate an API key (invalidate the old secret and return a new one).
     *
     * Rotation keeps the same logical key record but replaces its stored secret hash.
     * @param Request $request
     * @param ApiKey $apiKey
     * @return mixed
     */
    public function rotate(Request $request, ApiKey $apiKey)
    {
        $this->authorizeKey($request, $apiKey);

        $result = $this->service->rotate($apiKey);

        return response()->json([
            'api_key' => $result['api_key'],
            'key_prefix' => $result['model']->key_prefix,
            'expires_at' => $result['model']->expires_at,
            'resource' => ApiKeyResource::make($result['model']),
        ]);
    }

    /**
     * Ensure the API client belongs to the authenticated user.
     *
     * Uses 404 instead of 403 to avoid leaking the existence of other users' clients.
     * @param Request $request
     * @param ApiClient $apiClient
     * @return void
     */
    private function authorizeClient(Request $request, ApiClient $apiClient): void
    {
        if ((int) $apiClient->user_id !== (int) $request->user()->id) {
            abort(404);
        }
    }

    /**
     * Ensure the API key belongs to the authenticated user.
     *
     * Uses 404 instead of 403 to avoid leaking the existence of other users' keys.
     * @param Request $request
     * @param ApiKey $apiKey
     * @return void
     */
    private function authorizeKey(Request $request, ApiKey $apiKey): void
    {
        if ((int) ($apiKey->client?->user_id ?? 0) !== (int) $request->user()->id) {
            abort(404);
        }
    }
}
