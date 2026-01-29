<?php

namespace Tests\Unit;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Services\GatewayLogService;
use App\Jobs\DispatchGatewayLogJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GatewayLogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_log_job_and_redacts_sensitive_fields(): void
    {
        Queue::fake();

        config(['gateway.log_sink' => 'loki']);

        $context = new GatewayRequestContext(
            Request::create('/api/v1/ai/chat/completions', 'POST'),
            'chat.completions',
            [
                'provider' => 'groq',
                'model' => 'allam-2-7b',
                'api_key' => 'secret-key',
            ],
            []
        );

        app(GatewayLogService::class)->dispatch($context);

        Queue::assertPushed(DispatchGatewayLogJob::class, function (DispatchGatewayLogJob $job) {
            return $job->sink === 'loki'
                && ($job->payload['payload']['api_key'] ?? null) === '[redacted]';
        });
    }
}
