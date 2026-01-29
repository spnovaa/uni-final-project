<?php

namespace Tests\Unit;

use App\Domains\Gateway\Logging\LokiLogSink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LokiLogSinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_logs_to_loki_push_endpoint(): void
    {
        Http::fake();

        config([
            'gateway.log_sinks.loki.push_url' => 'https://logs.example.com/loki/api/v1/push',
            'gateway.log_sinks.loki.username' => 'user',
            'gateway.log_sinks.loki.api_key' => 'token',
            'gateway.log_sinks.loki.labels' => ['app' => 'gateway', 'env' => 'test'],
        ]);

        $payload = [
            'message' => 'gateway.request',
            'endpoint' => 'chat.completions',
            'status' => 200,
        ];

        app(LokiLogSink::class)->send($payload);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'https://logs.example.com/loki/api/v1/push'
                && isset($data['streams'][0]['stream']['app'])
                && isset($data['streams'][0]['values'][0][1]);
        });
    }
}
