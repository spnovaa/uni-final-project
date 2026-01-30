<?php

namespace App\Domains\Gateway\Services;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Jobs\DispatchGatewayLogJob;
use Illuminate\Support\Arr;

/**
 * Service layer for gateway log.
 */
class GatewayLogService
{
    private const REDACT_KEYS = [
        'authorization',
        'api_key',
        'x-api-key',
        'token',
        'password',
        'secret',
        'access_token',
    ];

    /**
     * Dispatch.
     * @param GatewayRequestContext $context
     * @return void
     */
    public function dispatch(GatewayRequestContext $context): void
    {
        $sink = config('gateway.log_sink');
        if (! $sink || $sink === 'none') {
            return;
        }

        $payload = $this->buildPayload($context);

        DispatchGatewayLogJob::dispatch($sink, $payload);
    }

    /**
     * Build payload.
     * @param GatewayRequestContext $context
     * @return array
     */
    private function buildPayload(GatewayRequestContext $context): array
    {
        $apiKey = $context->apiKey;
        $response = $context->providerResponse;

        $payload = [
            'timestamp' => now()->toISOString(),
            'request_id' => $context->requestId,
            'endpoint' => $context->endpoint,
            'provider' => $context->providerName,
            'model' => $context->modelKey,
            'status' => $context->status,
            'latency_ms' => $context->latencyMs(),
            'ip' => $context->request->ip(),
            'user_id' => $apiKey?->client?->user_id,
            'api_key_prefix' => $apiKey?->key_prefix,
            'payload' => $this->truncate($this->redact($context->payload)),
            'files' => $this->summarizeFiles($context->files),
            'response' => $this->truncate($this->summarizeResponse($response?->body, $response?->isBinary ?? false)),
            'usage' => $this->usageArray($context->usage),
            'estimated_usage' => $this->usageArray($context->estimatedUsage),
            'estimated_cost' => $context->estimatedTotalCost,
        ];

        return Arr::where($payload, fn ($value) => $value !== null);
    }

    /**
     * Summarize files.
     * @param array $files
     * @return array
     */
    private function summarizeFiles(array $files): array
    {
        $summary = [];
        foreach ($files as $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $summary[] = [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getClientMimeType(),
                ];
            }
        }

        return $summary;
    }

    /**
     * Summarize response.
     * @param mixed $body
     * @param bool $isBinary
     * @return array|string|null
     */
    private function summarizeResponse(mixed $body, bool $isBinary): array|string|null
    {
        if ($isBinary) {
            return [
                'binary' => true,
                'size' => is_string($body) ? strlen($body) : null,
            ];
        }

        return $body;
    }

    /**
     * Usage array.
     * @param ?\App\Domains\Gateway\DTOs\UsageMetrics $usage
     * @return ?array
     */
    private function usageArray(?\App\Domains\Gateway\DTOs\UsageMetrics $usage): ?array
    {
        if (! $usage) {
            return null;
        }

        return [
            'prompt_tokens' => $usage->promptTokens,
            'completion_tokens' => $usage->completionTokens,
            'total_tokens' => $usage->totalTokens,
            'images' => $usage->images,
            'audio_seconds' => $usage->audioSeconds,
            'audio_chars' => $usage->audioCharacters,
        ];
    }

    /**
     * Redact.
     * @param mixed $value
     * @return mixed
     */
    private function redact(mixed $value): mixed
    {
        if (is_array($value)) {
            $redacted = [];
            foreach ($value as $key => $item) {
                if (is_string($key) && $this->isSensitiveKey($key)) {
                    $redacted[$key] = '[redacted]';
                    continue;
                }

                $redacted[$key] = $this->redact($item);
            }

            return $redacted;
        }

        return $value;
    }

    /**
     * Is sensitive key.
     * @param string $key
     * @return bool
     */
    private function isSensitiveKey(string $key): bool
    {
        $key = strtolower($key);

        foreach (self::REDACT_KEYS as $candidate) {
            if ($key === $candidate || str_contains($key, $candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Truncate.
     * @param mixed $payload
     * @return mixed
     */
    private function truncate(mixed $payload): mixed
    {
        $maxBytes = (int) config('gateway.log_payload_max_bytes', 20000);

        if ($maxBytes <= 0) {
            return $payload;
        }

        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            return $payload;
        }

        if (strlen($encoded) <= $maxBytes) {
            return $payload;
        }

        return [
            'truncated' => true,
            'preview' => substr($encoded, 0, $maxBytes),
        ];
    }
}
