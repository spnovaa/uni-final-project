<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Support\OpenAiErrorResponder;
use Closure;

/**
 * Gateway pipeline step for enforce ip allowlist.
 */
class EnforceIpAllowlistPipe
{
    /**
     * Process the gateway context and continue the pipeline.
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
