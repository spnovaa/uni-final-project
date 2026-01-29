<?php

namespace Tests\Feature;

use App\Models\ApiClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_list_api_clients(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $create = $this->postJson('/api/v1/api-clients', [
            'name' => 'Primary Client',
        ]);

        $create->assertCreated()
            ->assertJson(['name' => 'Primary Client']);

        $list = $this->getJson('/api/v1/api-clients');
        $list->assertOk()
            ->assertJsonFragment(['name' => 'Primary Client']);

        $this->assertDatabaseHas('api_clients', [
            'user_id' => $user->id,
            'name' => 'Primary Client',
        ]);
    }
}
