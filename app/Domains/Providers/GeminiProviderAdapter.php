<?php

namespace App\Domains\Providers;

use App\Domains\Gateway\DTOs\GatewayRequestDto;
use App\Domains\Gateway\DTOs\ProviderResponse;
use App\Domains\Gateway\DTOs\UsageMetrics;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Adapter for Google Gemini native API.
 *
 * This adapter calls Gemini's `generateContent` / `embedContent` endpoints using the provider's
 * standard request/response format, then maps the result into OpenAI-compatible shapes so the
 * gateway API surface remains unchanged.
 */
class GeminiProviderAdapter implements ProviderAdapterInterface
{
    private const ENDPOINT_MAP = [
        'responses' => 'generateContent',
        'chat.completions' => 'generateContent',
        'embeddings' => 'embedContent',
    ];

    /**
     * Return true when the endpoint is supported by the Gemini adapter.
     * @param string $endpoint
     * @param ?string $model
     * @return bool
     */
    public function supports(string $endpoint, ?string $model): bool
    {
        return array_key_exists($endpoint, self::ENDPOINT_MAP);
    }

    /**
     * Send the request to Gemini and map the response into an OpenAI-compatible shape.
     *
     * Uses the `x-goog-api-key` header and provider config values for base URL and timeout.
     * @param GatewayRequestDto $request
     * @return ProviderResponse
     */
    public function send(GatewayRequestDto $request): ProviderResponse
    {
        $config = $request->providerConfig
            ?? config('gateway.providers.gemini', []);
        $apiKey = $config['api_key'] ?? null;
        $baseUrl = rtrim($config['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta', '/');
        $timeout = (int) ($config['timeout'] ?? 60);

        if (empty($apiKey)) {
            throw new RuntimeException('Gemini API key not configured.');
        }

        if (empty($request->model)) {
            throw new RuntimeException('Gemini model not specified.');
        }

        $client = Http::withHeaders(['x-goog-api-key' => $apiKey])
            ->acceptJson()
            ->timeout($timeout);

        if ($request->endpoint === 'embeddings') {
            [$url, $payload] = $this->buildEmbeddingsRequest($baseUrl, $request->model, $request->payload);
        } else {
            $payload = $this->buildGenerateContentRequest($request->endpoint, $request->payload);
            $url = $baseUrl.'/models/'.$request->model.':generateContent';
        }

        $response = $client->post($url, $payload);
        $body = $response->json() ?? [];

        if (! is_array($body)) {
            $body = [];
        }

        if ($response->status() >= 400) {
            return new ProviderResponse(
                $response->status(),
                $this->mapErrorResponse($body, $response->status()),
                $response->headers()
            );
        }

        $mapped = match ($request->endpoint) {
            'embeddings' => $this->mapEmbeddingsResponse($body, $request->model),
            'responses' => $this->mapResponsesResponse($body, $request->model),
            default => $this->mapChatResponse($body, $request->model),
        };

        return new ProviderResponse(
            $response->status(),
            $mapped,
            $response->headers()
        );
    }

    /**
     * Extract usage metrics from the mapped response body.
     *
     * The gateway normalizes Gemini responses into OpenAI-style `usage` arrays; this method reads
     * those values and returns a UsageMetrics DTO for billing.
     * @param ProviderResponse $response
     * @return ?UsageMetrics
     */
    public function extractUsage(ProviderResponse $response): ?UsageMetrics
    {
        if (! is_array($response->body)) {
            return null;
        }

        $usage = $response->body['usage'] ?? null;
        if (is_array($usage)) {
            $promptTokens = $usage['prompt_tokens'] ?? $usage['input_tokens'] ?? null;
            $completionTokens = $usage['completion_tokens'] ?? $usage['output_tokens'] ?? null;
            $totalTokens = $usage['total_tokens'] ?? null;

            return new UsageMetrics(
                $promptTokens,
                $completionTokens,
                $totalTokens
            );
        }

        // Backward-compatible support if a raw Gemini response is provided.
        $usage = $response->body['usageMetadata'] ?? null;
        if (! is_array($usage)) {
            return null;
        }

        return new UsageMetrics(
            $usage['promptTokenCount'] ?? null,
            $usage['candidatesTokenCount'] ?? null,
            $usage['totalTokenCount'] ?? null
        );
    }

    /**
     * Build generate content request.
     * @param string $endpoint
     * @param array $payload
     * @return array
     */
    private function buildGenerateContentRequest(string $endpoint, array $payload): array
    {
        $contents = [];
        $systemParts = [];

        if ($endpoint === 'responses') {
            $contents = $this->buildContentsFromInput($payload['input'] ?? null);
        }

        if (empty($contents)) {
            [$contents, $systemParts] = $this->buildContentsFromMessages($payload['messages'] ?? []);
        }

        if (empty($contents) && isset($payload['input'])) {
            $contents = $this->buildContentsFromInput($payload['input']);
        }

        $request = [
            'contents' => $contents,
        ];

        if (! empty($systemParts)) {
            $request['systemInstruction'] = [
                'parts' => $systemParts,
            ];
        }

        $generationConfig = $this->buildGenerationConfig($payload);
        if (! empty($generationConfig)) {
            $request['generationConfig'] = $generationConfig;
        }

        return $request;
    }

    /**
     * Build embeddings request.
     * @param string $baseUrl
     * @param string $model
     * @param array $payload
     * @return array
     */
    private function buildEmbeddingsRequest(string $baseUrl, string $model, array $payload): array
    {
        $input = $payload['input'] ?? '';

        if (is_array($input)) {
            $requests = [];
            foreach ($input as $item) {
                $parts = $this->extractParts($item);
                if (! empty($parts)) {
                    $requests[] = [
                        'content' => [
                            'parts' => $parts,
                        ],
                    ];
                }
            }

            return [
                $baseUrl.'/models/'.$model.':batchEmbedContents',
                ['requests' => $requests],
            ];
        }

        $parts = $this->extractParts($input);

        return [
            $baseUrl.'/models/'.$model.':embedContent',
            [
                'content' => [
                    'parts' => $parts,
                ],
            ],
        ];
    }

    /**
     * Build contents from messages.
     * @param array $messages
     * @return array
     */
    private function buildContentsFromMessages(array $messages): array
    {
        $contents = [];
        $systemParts = [];

        foreach ($messages as $message) {
            if (! is_array($message)) {
                continue;
            }

            $role = $message['role'] ?? 'user';
            $parts = $this->extractParts($message['content'] ?? '');

            if (empty($parts)) {
                continue;
            }

            if ($role === 'system') {
                $systemParts = array_merge($systemParts, $parts);
                continue;
            }

            $contents[] = [
                'role' => $role === 'assistant' ? 'model' : 'user',
                'parts' => $parts,
            ];
        }

        return [$contents, $systemParts];
    }

    /**
     * Build contents from input.
     * @param mixed $input
     * @return array
     */
    private function buildContentsFromInput(mixed $input): array
    {
        $parts = $this->extractParts($input);

        if (empty($parts)) {
            return [];
        }

        return [
            [
                'role' => 'user',
                'parts' => $parts,
            ],
        ];
    }

    /**
     * Extract parts.
     * @param mixed $content
     * @return array
     */
    private function extractParts(mixed $content): array
    {
        if (is_string($content)) {
            return [['text' => $content]];
        }

        if (is_array($content)) {
            $parts = [];

            foreach ($content as $item) {
                if (is_string($item)) {
                    $parts[] = ['text' => $item];
                    continue;
                }

                if (is_array($item)) {
                    $text = $item['text'] ?? $item['input_text'] ?? $item['content'] ?? null;
                    if ($text !== null && is_string($text)) {
                        $parts[] = ['text' => $text];
                        continue;
                    }

                    if (isset($item['type']) && $item['type'] === 'input_text' && isset($item['text'])) {
                        $parts[] = ['text' => (string) $item['text']];
                    }
                }
            }

            return $parts;
        }

        if ($content === null) {
            return [];
        }

        return [['text' => (string) $content]];
    }

    /**
     * Build generation config.
     * @param array $payload
     * @return array
     */
    private function buildGenerationConfig(array $payload): array
    {
        $config = [];
        $maxTokens = $payload['max_output_tokens'] ?? $payload['max_completion_tokens'] ?? $payload['max_tokens'] ?? null;
        if (is_numeric($maxTokens)) {
            $config['maxOutputTokens'] = (int) $maxTokens;
        }

        if (isset($payload['temperature']) && is_numeric($payload['temperature'])) {
            $config['temperature'] = (float) $payload['temperature'];
        }

        if (isset($payload['top_p']) && is_numeric($payload['top_p'])) {
            $config['topP'] = (float) $payload['top_p'];
        }

        return $config;
    }

    /**
     * Map chat response.
     * @param array $body
     * @param string $model
     * @return array
     */
    private function mapChatResponse(array $body, string $model): array
    {
        $choices = [];
        $candidates = $body['candidates'] ?? [];

        foreach ($candidates as $index => $candidate) {
            $text = $this->extractTextFromParts($candidate['content']['parts'] ?? []);
            $choices[] = [
                'index' => $index,
                'message' => [
                    'role' => 'assistant',
                    'content' => $text,
                ],
                'finish_reason' => $this->mapFinishReason($candidate['finishReason'] ?? null),
            ];
        }

        $response = [
            'id' => $body['responseId'] ?? 'chatcmpl_'.Str::uuid()->toString(),
            'object' => 'chat.completion',
            'created' => time(),
            'model' => $model,
            'choices' => $choices,
        ];

        $usage = $this->extractUsageMetadata($body);
        if ($usage) {
            $response['usage'] = [
                'prompt_tokens' => $usage['prompt_tokens'],
                'completion_tokens' => $usage['completion_tokens'],
                'total_tokens' => $usage['total_tokens'],
            ];
        }

        return $response;
    }

    /**
     * Map responses response.
     * @param array $body
     * @param string $model
     * @return array
     */
    private function mapResponsesResponse(array $body, string $model): array
    {
        $output = [];
        $candidates = $body['candidates'] ?? [];

        foreach ($candidates as $candidate) {
            $text = $this->extractTextFromParts($candidate['content']['parts'] ?? []);
            $output[] = [
                'id' => 'msg_'.Str::uuid()->toString(),
                'type' => 'message',
                'role' => 'assistant',
                'content' => [
                    [
                        'type' => 'output_text',
                        'text' => $text,
                    ],
                ],
            ];
        }

        $response = [
            'id' => $body['responseId'] ?? 'resp_'.Str::uuid()->toString(),
            'object' => 'response',
            'created_at' => time(),
            'status' => 'completed',
            'model' => $model,
            'output' => $output,
        ];

        $usage = $this->extractUsageMetadata($body);
        if ($usage) {
            $response['usage'] = [
                'input_tokens' => $usage['prompt_tokens'],
                'output_tokens' => $usage['completion_tokens'],
                'total_tokens' => $usage['total_tokens'],
            ];
        }

        return $response;
    }

    /**
     * Map embeddings response.
     * @param array $body
     * @param string $model
     * @return array
     */
    private function mapEmbeddingsResponse(array $body, string $model): array
    {
        $embeddings = [];
        if (isset($body['embeddings']) && is_array($body['embeddings'])) {
            $embeddings = $body['embeddings'];
        } elseif (isset($body['embedding'])) {
            $embeddings = [$body['embedding']];
        }

        $data = [];
        foreach ($embeddings as $index => $embedding) {
            $values = $embedding['values'] ?? $embedding['embedding'] ?? $embedding;
            $data[] = [
                'object' => 'embedding',
                'index' => $index,
                'embedding' => is_array($values) ? $values : [],
            ];
        }

        $response = [
            'object' => 'list',
            'data' => $data,
            'model' => $model,
        ];

        $usage = $this->extractUsageMetadata($body);
        if ($usage) {
            $response['usage'] = [
                'prompt_tokens' => $usage['prompt_tokens'],
                'total_tokens' => $usage['total_tokens'],
            ];
        }

        return $response;
    }

    /**
     * Extract usage metadata.
     * @param array $body
     * @return ?array
     */
    private function extractUsageMetadata(array $body): ?array
    {
        $usage = $body['usageMetadata'] ?? null;
        if (! is_array($usage)) {
            return null;
        }

        $prompt = $usage['promptTokenCount'] ?? null;
        $completion = $usage['candidatesTokenCount'] ?? null;
        $total = $usage['totalTokenCount'] ?? null;

        if ($prompt === null && $completion === null && $total === null) {
            return null;
        }

        return [
            'prompt_tokens' => $prompt,
            'completion_tokens' => $completion,
            'total_tokens' => $total,
        ];
    }

    /**
     * Extract text from parts.
     * @param array $parts
     * @return string
     */
    private function extractTextFromParts(array $parts): string
    {
        $texts = [];
        foreach ($parts as $part) {
            if (is_array($part) && isset($part['text'])) {
                $texts[] = $part['text'];
            }
        }

        return trim(implode(' ', $texts));
    }

    /**
     * Map finish reason.
     * @param ?string $reason
     * @return ?string
     */
    private function mapFinishReason(?string $reason): ?string
    {
        if (! $reason) {
            return null;
        }

        return match (strtoupper($reason)) {
            'STOP' => 'stop',
            'MAX_TOKENS' => 'length',
            'SAFETY', 'RECITATION' => 'content_filter',
            default => strtolower($reason),
        };
    }

    /**
     * Map error response.
     * @param array $body
     * @param int $status
     * @return array
     */
    private function mapErrorResponse(array $body, int $status): array
    {
        $message = $body['error']['message'] ?? $body['message'] ?? 'Upstream provider error.';
        $type = $status >= 500 ? 'server_error' : 'invalid_request_error';

        return [
            'error' => [
                'message' => $message,
                'type' => $type,
                'param' => null,
                'code' => null,
            ],
        ];
    }
}
