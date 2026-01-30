<?php

namespace App\Domains\Gateway\Logging;

use Illuminate\Support\Facades\Http;

/**
 * Class LokiLogSink.
 */
class LokiLogSink implements LogSinkInterface
{
    /**
     * Send.
     * @param array $payload
     * @return void
     */
    public function send(array $payload): void
    {
        $config = config('gateway.log_sinks.loki', []);
        $pushUrl = $config['push_url'] ?? null;

        if (! $pushUrl) {
            return;
        }

        $labels = array_merge([
            'app' => (string) config('app.name', 'gateway'),
            'env' => (string) config('app.env', 'local'),
            'service' => 'gateway',
        ], $config['labels'] ?? []);

        $line = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($line === false) {
            $line = json_encode(['message' => 'Unable to encode log payload.']);
        }

        $body = [
            'streams' => [
                [
                    'stream' => $labels,
                    'values' => [
                        [$this->nowNano(), $line],
                    ],
                ],
            ],
        ];

        $client = Http::timeout((int) ($config['timeout'] ?? 5));

        if (! empty($config['username']) && ! empty($config['api_key'])) {
            $client = $client->withBasicAuth($config['username'], $config['api_key']);
        }

        if (! empty($config['headers']) && is_array($config['headers'])) {
            $client = $client->withHeaders($config['headers']);
        }

        $client->post($pushUrl, $body);
    }

    /**
     * Now nano.
     * @return string
     */
    private function nowNano(): string
    {
        $seconds = microtime(true);
        $nanoseconds = (int) round($seconds * 1_000_000_000);

        return (string) $nanoseconds;
    }
}
