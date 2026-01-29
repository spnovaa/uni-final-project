<?php

namespace Tests\Feature;

use App\Domains\Keys\Services\ApiKeyService;
use App\Models\ApiClient;
use App\Models\ApiKey;
use App\Models\Provider;
use App\Models\ProviderModel;
use App\Models\User;
use App\Services\Billing\Wallet\WalletServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['gateway.providers.openai.api_key' => 'test-key']);
        config(['gateway.providers.openai.base_url' => 'https://api.openai.com/v1']);
    }

    public function test_missing_api_key_returns_auth_error(): void
    {
        $response = $this->postJson('/api/v1/ai/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [['role' => 'user', 'content' => 'Hi']],
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error.type', 'authentication_error');
    }

    public function test_chat_completions_with_bearer_token(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'id' => 'chatcmpl_test',
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

        $apiKey = $this->createApiKey();

        $response = $this->withHeader('Authorization', 'Bearer '.$apiKey)
            ->postJson('/api/v1/ai/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [['role' => 'user', 'content' => 'Hi']],
            ]);

        $response->assertOk()
            ->assertJsonPath('id', 'chatcmpl_test')
            ->assertJsonPath('object', 'chat.completion');
    }

    public function test_responses_endpoint(): void
    {
        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'id' => 'resp_test',
                'object' => 'response',
                'model' => 'gpt-4o-mini',
                'output' => [
                    [
                        'type' => 'message',
                        'role' => 'assistant',
                        'content' => [
                            ['type' => 'output_text', 'text' => 'Hello from responses'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $apiKey = $this->createApiKey();

        $response = $this->withHeader('Authorization', 'Bearer '.$apiKey)
            ->postJson('/api/v1/ai/responses', [
                'model' => 'gpt-4o-mini',
                'input' => 'Hello',
            ]);

        $response->assertOk()
            ->assertJsonPath('id', 'resp_test')
            ->assertJsonPath('object', 'response');
    }

    public function test_embeddings_with_x_api_key(): void
    {
        Http::fake([
            'https://api.openai.com/v1/embeddings' => Http::response([
                'object' => 'list',
                'data' => [
                    [
                        'object' => 'embedding',
                        'index' => 0,
                        'embedding' => [0.1, 0.2],
                    ],
                ],
                'model' => 'text-embedding-3-small',
                'usage' => [
                    'prompt_tokens' => 4,
                    'total_tokens' => 4,
                ],
            ], 200),
        ]);

        $apiKey = $this->createApiKey();

        $response = $this->withHeader('X-API-Key', $apiKey)
            ->postJson('/api/v1/ai/embeddings', [
                'model' => 'text-embedding-3-small',
                'input' => 'hello',
            ]);

        $response->assertOk()
            ->assertJsonPath('object', 'list');
    }

    public function test_images_generations(): void
    {
        Http::fake([
            'https://api.openai.com/v1/images/generations' => Http::response([
                'created' => time(),
                'data' => [
                    ['url' => 'https://example.com/image.png'],
                ],
            ], 200),
        ]);

        $apiKey = $this->createApiKey();

        $response = $this->withHeader('Authorization', 'Bearer '.$apiKey)
            ->postJson('/api/v1/ai/images/generations', [
                'model' => 'gpt-image-1',
                'prompt' => 'A test image',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.0.url', 'https://example.com/image.png');
    }

    public function test_audio_transcriptions_requires_file(): void
    {
        $apiKey = $this->createApiKey();

        $response = $this->withHeader('Authorization', 'Bearer '.$apiKey)
            ->postJson('/api/v1/ai/audio/transcriptions', [
                'model' => 'whisper-1',
            ]);

        $response->assertStatus(400)
            ->assertJsonPath('error.type', 'invalid_request_error');
    }

    public function test_audio_transcriptions_with_file(): void
    {
        Http::fake([
            'https://api.openai.com/v1/audio/transcriptions' => Http::response([
                'text' => 'hello world',
            ], 200),
        ]);

        $apiKey = $this->createApiKey();

        $file = UploadedFile::fake()->create('audio.mp3', 10, 'audio/mpeg');

        $response = $this->withHeader('Authorization', 'Bearer '.$apiKey)
            ->post('/api/v1/ai/audio/transcriptions', [
                'model' => 'whisper-1',
                'file' => $file,
            ]);

        $response->assertOk()
            ->assertJsonPath('text', 'hello world');
    }

    public function test_audio_speech_returns_binary(): void
    {
        Http::fake([
            'https://api.openai.com/v1/audio/speech' => Http::response(
                'binary-audio',
                200,
                ['Content-Type' => 'audio/mpeg']
            ),
        ]);

        $apiKey = $this->createApiKey();

        $response = $this->withHeader('Authorization', 'Bearer '.$apiKey)
            ->postJson('/api/v1/ai/audio/speech', [
                'model' => 'gpt-4o-mini-tts',
                'input' => 'Hello there',
                'voice' => 'alloy',
            ]);

        $response->assertOk();
        $this->assertSame('audio/mpeg', $response->headers->get('Content-Type'));
        $this->assertSame('binary-audio', $response->getContent());
    }

    public function test_rate_limit_is_enforced(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'id' => 'chatcmpl_test',
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
            ], 200),
        ]);

        $apiKey = $this->createApiKey();
        $model = ApiKey::query()->firstOrFail();
        $model->rate_limit_per_min = 1;
        $model->save();

        $first = $this->withHeader('Authorization', 'Bearer '.$apiKey)
            ->postJson('/api/v1/ai/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [['role' => 'user', 'content' => 'Hi']],
            ]);

        $first->assertOk();

        $second = $this->withHeader('Authorization', 'Bearer '.$apiKey)
            ->postJson('/api/v1/ai/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [['role' => 'user', 'content' => 'Hi again']],
            ]);

        $second->assertStatus(429)
            ->assertJsonPath('error.type', 'rate_limit_error');
    }

    private function createApiKey(): string
    {
        $user = User::factory()->create();
        $client = ApiClient::query()->create([
            'user_id' => $user->id,
            'name' => 'Gateway Client',
            'status' => 'active',
        ]);

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

        app(WalletServiceInterface::class)->topup($user, 10, 'test_topup');

        $result = app(ApiKeyService::class)->create($client);

        return $result['api_key'];
    }
}
