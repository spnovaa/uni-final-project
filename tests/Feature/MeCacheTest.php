<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_cache_is_filled_and_invalidated(): void
    {
        config(['cache.default' => 'array']);
        Cache::flush();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me')->assertOk();

        $this->assertTrue(Cache::has('user:profile:'.$user->id));

        $this->patchJson('/api/v1/me', [
            'name' => 'Updated',
        ])->assertOk();

        $this->assertFalse(Cache::has('user:profile:'.$user->id));
    }
}
