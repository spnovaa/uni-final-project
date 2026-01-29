<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me');

        $response->assertOk()
            ->assertJson(['id' => $user->id, 'email' => $user->email]);
    }

    public function test_can_update_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/me', [
            'name' => 'Updated User',
        ]);

        $response->assertOk()
            ->assertJson(['name' => 'Updated User']);
    }

    public function test_can_update_profile_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->patchJson('/api/v1/me', [
            'profile_image' => $file,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['profile_image_url']);

        $user->refresh();
        $this->assertNotNull($user->profile_image_path);
        Storage::disk('public')->assertExists($user->profile_image_path);
    }
}
