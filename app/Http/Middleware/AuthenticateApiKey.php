<?php

namespace App\Http\Middleware;

use App\Domains\Gateway\Support\OpenAiErrorResponder;
use App\Models\ApiKey;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthenticateApiKey
{
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

    private function extractPrefix(string $token): string
    {
        $token = Str::startsWith($token, 'gk_') ? substr($token, 3) : $token;

        return substr($token, 0, 8);
    }

    private function hashToken(string $token): string
    {
        return hash_hmac('sha256', $token, config('app.key'));
    }
}
