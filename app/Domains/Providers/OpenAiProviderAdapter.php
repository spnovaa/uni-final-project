<?php

namespace App\Domains\Providers;

use App\Domains\Gateway\DTOs\GatewayRequestDto;
use App\Domains\Gateway\DTOs\ProviderResponse;
use App\Domains\Gateway\DTOs\UsageMetrics;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class OpenAiProviderAdapter implements ProviderAdapterInterface
{
    private const ENDPOINT_MAP = [
        'responses' => '/responses',
        'chat.completions' => '/chat/completions',
        'embeddings' => '/embeddings',
        'images.generations' => '/images/generations',
        'audio.transcriptions' => '/audio/transcriptions',
        'audio.speech' => '/audio/speech',
    ];

    public function supports(string $endpoint, ?string $model): bool
    {
        return array_key_exists($endpoint, self::ENDPOINT_MAP);
    }

    public function send(GatewayRequestDto $request): ProviderResponse
    {
        $config = $request->providerConfig
            ?? config('gateway.providers.'.($request->provider ?: 'openai'), []);
        $apiKey = $config['api_key'] ?? null;
        $baseUrl = rtrim($config['base_url'] ?? 'https://api.openai.com/v1', '/');
        $timeout = (int) ($config['timeout'] ?? 60);

        if (empty($apiKey)) {
            throw new RuntimeException('OpenAI API key not configured.');
        }

        $endpoint = self::ENDPOINT_MAP[$request->endpoint] ?? null;
        if ($endpoint === null) {
            throw new RuntimeException('Unsupported OpenAI endpoint: '.$request->endpoint);
        }

        $client = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout($timeout);

        $url = $baseUrl.$endpoint;

        if ($request->endpoint === 'audio.transcriptions') {
            $file = $this->extractFile($request->files);
            $payload = $request->payload;
            unset($payload['file']);

            $pending = $client;
            if ($file instanceof UploadedFile) {
                $path = $file->getRealPath() ?: $file->getPathname();
                $stream = $path ? fopen($path, 'r') : false;
                if ($stream === false) {
                    throw new RuntimeException('Unable to read uploaded file contents.');
                }

                $pending = $client->attach(
                    'file',
                    $stream,
                    $file->getClientOriginalName()
                );
            }

            $response = $pending->post($url, $payload);
        } else {
            $response = $client->post($url, $request->payload);
        }

        $contentType = $response->header('Content-Type');
        $isJson = $contentType && Str::contains($contentType, 'application/json');

        if ($isJson) {
            return new ProviderResponse(
                $response->status(),
                $response->json() ?? [],
                $response->headers()
            );
        }

        return new ProviderResponse(
            $response->status(),
            $response->body(),
            $response->headers(),
            true
        );
    }

    public function extractUsage(ProviderResponse $response): ?UsageMetrics
    {
        if (! is_array($response->body)) {
            return null;
        }

        $images = null;
        if (isset($response->body['data']) && is_array($response->body['data'])) {
            $images = count($response->body['data']);
        }

        $usage = $response->body['usage'] ?? null;
        if (! is_array($usage)) {
            if ($images === null) {
                return null;
            }

            return new UsageMetrics(
                null,
                null,
                null,
                $images
            );
        }

        return new UsageMetrics(
            $usage['prompt_tokens'] ?? null,
            $usage['completion_tokens'] ?? null,
            $usage['total_tokens'] ?? null,
            $images
        );
    }

    private function extractFile(array $files): ?UploadedFile
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                return $file;
            }
        }

        return null;
    }
}
