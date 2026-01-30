<?php

namespace App\Domains\Gateway\Services;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Jobs\DispatchGatewayLogJob;
use Illuminate\Support\Arr;

/**
 * Build and dispatch structured gateway logs to external sinks.
 *
 * This service builds a log payload from the gateway context, redacts sensitive fields,
 * truncates large payloads, and dispatches a queued job that sends the log to the selected sink.
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
     * Dispatch an external log job for this gateway request (best effort).
     *
     * No log is sent when `gateway.log_sink` is `none` or empty.
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
     * Build a sanitized log payload from the gateway context.
     *
     * Includes request metadata, redacted payload, response summary, and usage metrics.
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
     * Summarize uploaded files without including their full contents.
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
     * Summarize the provider response for logging.
     *
     * Binary responses are replaced with a size summary to avoid logging raw bytes.
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
     * Convert UsageMetrics into a simple array suitable for JSON logging.
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
     * Redact sensitive values by key name in nested arrays.
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
     * Check whether a key name is considered sensitive for logging.
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
     * Truncate the payload when its JSON encoding exceeds the configured byte limit.
     *
     * This avoids sending extremely large payloads to external logging services.
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
