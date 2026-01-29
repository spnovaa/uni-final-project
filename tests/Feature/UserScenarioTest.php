<?php

namespace Tests\Feature;

use App\Domains\Keys\Services\ApiKeyService;
use App\Models\ApiClient;
use App\Models\Provider;
use App\Models\ProviderModel;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Billing\Wallet\WalletServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserScenarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_wallet_can_call_and_is_charged(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient($user);
        $apiKey = app(ApiKeyService::class)->create($client)['api_key'];

        $this->seedProvider();
        app(WalletServiceInterface::class)->topup($user, 5, 'test_topup');

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'id' => 'chatcmpl_scenario',
                'object' => 'chat.completion',
                'created' => time(),
                'model' => 'gpt-4o-mini',
                'choices' => [
                    [
                        'index' => 0,
                        'message' => ['role' => 'assistant', 'content' => 'Hello'],
                        'finish_reason' => 'stop',
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 1000,
                    'completion_tokens' => 1000,
                    'total_tokens' => 2000,
                ],
            ], 200),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$apiKey)
            ->postJson('/api/v1/ai/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [['role' => 'user', 'content' => 'Hello']],
            ]);

        $response->assertOk();

        $user->refresh();
        $this->assertEquals(3.5, (float) $user->wallet->balance);
    }

    public function test_user_with_insufficient_wallet_is_blocked_before_request(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient($user);
        $apiKey = app(ApiKeyService::class)->create($client)['api_key'];

        $this->seedProvider();

        Http::fake();

        $response = $this->withHeader('Authorization', 'Bearer '.$apiKey)
            ->postJson('/api/v1/ai/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [[
                    'role' => 'user',
                    'content' => str_repeat('A', 20000),
                ]],
                'max_tokens' => 1000,
            ]);

        $response->assertStatus(402)
            ->assertJsonPath('error.type', 'insufficient_quota');

        Http::assertNothingSent();
    }

    public function test_user_with_subscription_included_credits_can_call_without_wallet(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient($user);
        $apiKey = app(ApiKeyService::class)->create($client)['api_key'];

        $this->seedProvider();

        $plan = SubscriptionPlan::factory()->create([
            'included_credits' => 100,
            'status' => 'active',
        ]);

        Subscription::query()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'renewal_at' => now()->addMonth(),
        ]);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'id' => 'chatcmpl_sub',
                'object' => 'chat.completion',
                'created' => time(),
                'model' => 'gpt-4o-mini',
                'choices' => [
                    [
                        'index' => 0,
                        'message' => ['role' => 'assistant', 'content' => 'Hello'],
                        'finish_reason' => 'stop',
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 10,
                    'completion_tokens' => 5,
                    'total_tokens' => 15,
                ],
            ], 200),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$apiKey)
            ->postJson('/api/v1/ai/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [['role' => 'user', 'content' => 'Hello']],
            ]);

        $response->assertOk();
    }

    public function test_invalid_payload_returns_openai_style_error(): void
    {
        $user = User::factory()->create();
        $client = $this->createClient($user);
        $apiKey = app(ApiKeyService::class)->create($client)['api_key'];

        $this->seedProvider();
        app(WalletServiceInterface::class)->topup($user, 5, 'test_topup');

        $response = $this->withHeader('Authorization', 'Bearer '.$apiKey)
            ->postJson('/api/v1/ai/chat/completions', [
                'model' => 'gpt-4o-mini',
            ]);

        $response->assertStatus(400)
            ->assertJsonPath('error.type', 'invalid_request_error');
    }

    public function test_playground_page_is_available(): void
    {
        $response = $this->get('/playground');
        $response->assertOk();
    }

    private function createClient(User $user): ApiClient
    {
        return ApiClient::query()->create([
            'user_id' => $user->id,
            'name' => 'Scenario Client',
            'status' => 'active',
        ]);
    }

    private function seedProvider(): void
    {
        $provider = Provider::query()->create([
            'name' => 'openai',
            'type' => 'openai_compatible',
            'base_url' => 'https://api.openai.com/v1',
            'status' => 'active',
            'priority' => 0,
            'config_encrypted' => [
                'api_key' => 'test-key',
                'base_url' => 'https://api.openai.com/v1',
                'timeout' => 60,
            ],
        ]);

        ProviderModel::query()->create([
            'provider_id' => $provider->id,
            'model_key' => 'gpt-4o-mini',
            'pricing_config' => [
                'input_cost_per_1k' => 0.5,
                'output_cost_per_1k' => 1.0,
            ],
            'status' => 'active',
        ]);
    }
}
