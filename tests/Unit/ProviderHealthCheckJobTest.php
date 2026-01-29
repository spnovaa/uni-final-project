<?php

namespace Tests\Unit;

use App\Jobs\ProviderHealthCheckJob;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProviderHealthCheckJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_provider_health(): void
    {
        Http::fake([
            'https://api.openai.com/v1/models' => Http::response(['data' => []], 200),
        ]);

        $provider = Provider::query()->create([
            'name' => 'openai',
            'type' => 'openai_compatible',
            'base_url' => 'https://api.openai.com/v1',
            'status' => 'active',
            'priority' => 0,
            'config_encrypted' => [
                'api_key' => 'test-key',
            ],
        ]);

        (new ProviderHealthCheckJob($provider->id))->handle(app(\App\Domains\Gateway\Services\ProviderRegistry::class));

        $this->assertDatabaseHas('provider_health_checks', [
            'provider_id' => $provider->id,
            'status' => 'up',
        ]);
    }
}
