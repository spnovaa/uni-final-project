<?php

namespace Tests\Feature;

use App\Domains\Keys\Services\ApiKeyService;
use App\Models\ApiClient;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_list_revoke_and_rotate_api_keys(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $client = ApiClient::query()->create([
            'user_id' => $user->id,
            'name' => 'Primary',
            'status' => 'active',
        ]);

        $create = $this->postJson("/api/v1/api-clients/{$client->id}/keys", [
            'scopes' => ['ai:chat'],
        ]);

        $create->assertCreated()
            ->assertJsonStructure(['api_key', 'key_prefix']);

        $list = $this->getJson("/api/v1/api-clients/{$client->id}/keys");
        $list->assertOk()
            ->assertJsonFragment(['key_prefix' => $create->json('key_prefix')]);

        $apiKey = ApiKey::query()->firstOrFail();

        $revoke = $this->postJson("/api/v1/api-keys/{$apiKey->id}/revoke");
        $revoke->assertOk()->assertJson(['status' => 'revoked']);

        $rotate = $this->postJson("/api/v1/api-keys/{$apiKey->id}/rotate");
        $rotate->assertOk()
            ->assertJsonStructure(['api_key', 'key_prefix']);
    }
}
