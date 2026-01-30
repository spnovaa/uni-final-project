<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Support\OpenAiErrorResponder;
use Closure;

/**
 * Enforce API key IP allowlisting for gateway requests.
 *
 * If the API key has `allowed_ips` configured, this pipe checks the request IP and
 * blocks the request with an OpenAI-style authorization error when the IP is not allowed.
 */
class EnforceIpAllowlistPipe
{
    /**
     * Validate the caller IP against the API key allowlist (if configured).
     *
     * On mismatch, sets an OpenAI-compatible error response and stops the pipeline.
     * @param GatewayRequestContext $context
     * @param Closure $next
     * @return mixed
     */
    public function handle(GatewayRequestContext $context, Closure $next)
    {
        $apiKey = $context->apiKey;

        if ($apiKey && ! empty($apiKey->allowed_ips)) {
            $allowed = array_filter((array) $apiKey->allowed_ips);
            $ip = $context->request->ip();

            if ($ip && ! in_array($ip, $allowed, true)) {
                $context->normalizedResponse = OpenAiErrorResponder::authorizationError('IP address is not allowed.')->getData(true);
                $context->status = 403;

                return $context;
            }
        }

        return $next($context);
    }
}
