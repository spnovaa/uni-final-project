<?php

namespace Tests\Unit;

use App\Jobs\AggregateDailyUsageJob;
use App\Models\ApiClient;
use App\Models\ApiKey;
use App\Models\GatewayRequest;
use App\Models\Provider;
use App\Models\ProviderModel;
use App\Models\UsageRecord;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AggregateDailyUsageJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_aggregates_daily_usage_rollups(): void
    {
        $user = User::factory()->create();
        $client = ApiClient::query()->create([
            'user_id' => $user->id,
            'name' => 'Client',
            'status' => 'active',
        ]);

        $apiKey = ApiKey::query()->create([
            'api_client_id' => $client->id,
            'key_prefix' => 'testkey',
            'key_hash' => hash_hmac('sha256', 'raw-key', config('app.key')),
        ]);

        $provider = Provider::query()->create([
            'name' => 'rollup-provider',
            'type' => 'openai_compatible',
            'base_url' => 'https://example.com',
            'status' => 'active',
            'priority' => 0,
        ]);

        $providerModel = ProviderModel::query()->create([
            'provider_id' => $provider->id,
            'model_key' => 'rollup-model',
            'status' => 'active',
        ]);

        $request = GatewayRequest::query()->create([
            'api_key_id' => $apiKey->id,
            'user_id' => $user->id,
            'provider_id' => $provider->id,
            'provider_model_id' => $providerModel->id,
            'endpoint' => 'chat.completions',
            'status' => '200',
        ]);

        $date = CarbonImmutable::now()->toDateString();

        UsageRecord::query()->create([
            'gateway_request_id' => $request->id,
            'metric' => 'tokens_in',
            'quantity' => 200,
            'unit_cost' => 0.0001,
            'total_cost' => 0.02,
            'created_at' => CarbonImmutable::now(),
        ]);

        (new AggregateDailyUsageJob($date))->handle();

        $this->assertDatabaseHas('daily_usage_rollups', [
            'date' => $date,
            'metric' => 'tokens_in',
        ]);
    }
}
