<?php

namespace App\Http\Middleware;

use App\Domains\Gateway\Support\OpenAiErrorResponder;
use App\Models\ApiKey;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Authenticate OpenAI-compatible gateway requests using a project API key.
 *
 * This middleware:
 * - Extracts the API key token from `Authorization: Bearer ...` or `X-API-Key`.
 * - Validates the token against the stored hashed secret (prefix + HMAC).
 * - Enforces revoked/expired keys, client/user status, and IP allowlisting.
 * - Attaches the resolved ApiKey model to the request for the gateway pipeline to consume.
 */
class AuthenticateApiKey
{
    /**
     * Authenticate the request and short-circuit with OpenAI-style errors when invalid.
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractToken($request);

        if (! $token) {
            return OpenAiErrorResponder::authenticationError('Missing API key.');
        }

        $prefix = $this->extractPrefix($token);
        $candidateKeys = ApiKey::query()
            ->where('key_prefix', $prefix)
            ->get();

        $apiKey = $candidateKeys->first(function (ApiKey $key) use ($token) {
            return hash_equals($key->key_hash, $this->hashToken($token));
        });

        if (! $apiKey) {
            return OpenAiErrorResponder::authenticationError('Incorrect API key provided.');
        }

        if ($apiKey->revoked_at !== null) {
            return OpenAiErrorResponder::authenticationError('API key has been revoked.');
        }

        if ($apiKey->expires_at !== null && CarbonImmutable::parse($apiKey->expires_at)->isPast()) {
            return OpenAiErrorResponder::authenticationError('API key has expired.');
        }

        $client = $apiKey->client;
        if (! $client || $client->status !== 'active') {
            return OpenAiErrorResponder::authenticationError('API client is inactive.');
        }

        $user = $client->user;
        if ($user && $user->status !== 'active') {
            return OpenAiErrorResponder::authenticationError('User account is inactive.');
        }

        if (! empty($apiKey->allowed_ips)) {
            $ip = $request->ip();
            if ($ip && ! in_array($ip, $apiKey->allowed_ips, true)) {
                return OpenAiErrorResponder::authorizationError('IP address not allowed.');
            }
        }

        $request->attributes->set('apiKey', $apiKey);

        return $next($request);
    }

    /**
     * Extract the API key token from supported headers.
     * @param Request $request
     * @return ?string
     */
    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization');
        if ($header && Str::startsWith($header, 'Bearer ')) {
            return trim(substr($header, 7));
        }

        $headerKey = $request->header('X-API-Key');
        if ($headerKey) {
            return trim($headerKey);
        }

        return null;
    }

    /**
     * Extract the stored lookup prefix from a raw API key token.
     *
     * The prefix allows fast DB lookup before verifying the full token hash.
     * @param string $token
     * @return string
     */
    private function extractPrefix(string $token): string
    {
        $token = Str::startsWith($token, 'gk_') ? substr($token, 3) : $token;

        return substr($token, 0, 8);
    }

    /**
     * Hash a token for comparison against the stored secret hash.
     *
     * Uses HMAC with the application key so plaintext secrets are never stored.
     * @param string $token
     * @return string
     */
    private function hashToken(string $token): string
    {
        return hash_hmac('sha256', $token, config('app.key'));
    }
}
