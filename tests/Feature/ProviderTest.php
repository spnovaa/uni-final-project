<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_provider_and_models(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $create = $this->postJson('/api/v1/providers', [
            'name' => 'openrouter',
            'type' => 'openai_compatible',
            'base_url' => 'https://openrouter.ai/api/v1',
            'api_key' => 'test-key',
            'status' => 'active',
        ]);

        $create->assertCreated()
            ->assertJsonPath('name', 'openrouter');

        $providerId = $create->json('id');

        $models = $this->postJson("/api/v1/providers/{$providerId}/models", [
            'model_key' => 'gpt-4o-mini',
            'capabilities' => ['chat' => true],
            'pricing_config' => ['input_token' => 0.0005, 'output_token' => 0.001],
        ]);

        $models->assertCreated()
            ->assertJsonPath('model_key', 'gpt-4o-mini');

        $list = $this->getJson("/api/v1/providers/{$providerId}/models");
        $list->assertOk()
            ->assertJsonFragment(['model_key' => 'gpt-4o-mini']);
    }
}
