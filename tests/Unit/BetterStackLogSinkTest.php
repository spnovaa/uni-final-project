<?php

namespace Tests\Unit;

use App\Domains\Gateway\Logging\BetterStackLogSink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BetterStackLogSinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_logs_to_betterstack(): void
    {
        Http::fake();

        config([
            'gateway.log_sinks.betterstack.ingest_host' => 'in.logs.betterstack.com',
            'gateway.log_sinks.betterstack.source_token' => 'token',
        ]);

        $payload = [
            'message' => 'gateway.request',
            'level' => 'info',
        ];

        app(BetterStackLogSink::class)->send($payload);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://in.logs.betterstack.com'
                && $request->hasHeader('Authorization', 'Bearer token');
        });
    }
}
