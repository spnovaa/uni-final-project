<?php

namespace App\Domains\Gateway\Logging;

use Illuminate\Support\Facades\Http;

/**
 * Better Stack log sink.
 *
 * Sends structured log payloads to Better Stack ingestion using the configured host and source token.
 */
class BetterStackLogSink implements LogSinkInterface
{
    /**
     * Send a log payload to Better Stack.
     * @param array $payload
     * @return void
     */
    public function send(array $payload): void
    {
        $config = config('gateway.log_sinks.betterstack', []);
        $host = $config['ingest_host'] ?? null;
        $token = $config['source_token'] ?? null;

        if (! $host || ! $token) {
            return;
        }

        $url = 'https://'.rtrim($host, '/');

        $body = [
            'message' => $payload['message'] ?? 'gateway.request',
            'level' => $payload['level'] ?? 'info',
            'time' => $payload['timestamp'] ?? now()->toISOString(),
            'context' => $payload,
        ];

        $client = Http::timeout((int) ($config['timeout'] ?? 5))
            ->withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json',
            ]);

        if (! empty($config['headers']) && is_array($config['headers'])) {
            $client = $client->withHeaders($config['headers']);
        }

        $client->post($url, $body);
    }
}
