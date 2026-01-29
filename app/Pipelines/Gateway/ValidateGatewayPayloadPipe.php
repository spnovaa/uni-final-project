<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Support\OpenAiErrorResponder;
use Closure;
use Illuminate\Support\Facades\Validator;

class ValidateGatewayPayloadPipe
{
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

    private function rulesForEndpoint(string $endpoint): array
    {
        return match ($endpoint) {
            'responses' => [],
            'chat.completions' => [
                'model' => ['required', 'string'],
                'messages' => ['required', 'array'],
            ],
            'embeddings' => [
                'model' => ['required', 'string'],
                'input' => ['required'],
            ],
            'images.generations' => [
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
    }
}
