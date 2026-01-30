<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Support\OpenAiErrorResponder;
use Closure;
use Illuminate\Support\Facades\Validator;

/**
 * Validate basic request payload structure for OpenAI-compatible gateway endpoints.
 *
 * This pipe performs light validation (required fields per endpoint) so the gateway can
 * fail fast with OpenAI-style `invalid_request_error` responses before any provider call.
 */
class ValidateGatewayPayloadPipe
{
    /**
     * Validate payload fields for the current endpoint and short-circuit on invalid input.
     *
     * - Uses endpoint-specific rules for required fields (e.g. `messages` for chat).
     * - Validates multipart requirements such as `file` for audio transcriptions.
     * @param GatewayRequestContext $context
     * @param Closure $next
     * @return mixed
     */
    public function handle(GatewayRequestContext $context, Closure $next)
    {
        $rules = $this->rulesForEndpoint($context->endpoint);

        if (! empty($rules)) {
            $validator = Validator::make($context->payload, $rules);

            if ($validator->fails()) {
                $firstError = $validator->errors()->first();
                $context->normalizedResponse = OpenAiErrorResponder::invalidRequest($firstError)->getData(true);
                $context->status = 400;

                return $context;
            }
        }

        if ($context->endpoint === 'audio.transcriptions') {
            if (empty($context->files)) {
                $context->normalizedResponse = OpenAiErrorResponder::invalidRequest('The file field is required.', 'file')->getData(true);
                $context->status = 400;

                return $context;
            }
        }

        return $next($context);
    }

    /**
     * Build the validation rules for a given OpenAI-compatible endpoint.
     * @param string $endpoint
     * @return array
     */
    private function rulesForEndpoint(string $endpoint): array
    {
        $baseRules = [
            'provider' => ['required', 'string'],
        ];

        $endpointRules = match ($endpoint) {
            'responses' => [
                'model' => ['required', 'string'],
            ],
            'chat.completions' => [
                'model' => ['required', 'string'],
                'messages' => ['required', 'array'],
            ],
            'embeddings' => [
                'model' => ['required', 'string'],
                'input' => ['required'],
            ],
            'images.generations' => [
                'model' => ['required', 'string'],
                'prompt' => ['required', 'string'],
            ],
            'audio.transcriptions' => [
                'model' => ['required', 'string'],
            ],
            'audio.speech' => [
                'model' => ['required', 'string'],
                'input' => ['required', 'string'],
                'voice' => ['required', 'string'],
            ],
            default => [],
        };

        return array_merge($baseRules, $endpointRules);
    }
}
