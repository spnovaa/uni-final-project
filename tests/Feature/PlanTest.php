<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_list_plans(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $create = $this->postJson('/api/v1/plans', [
            'name' => 'Starter',
            'price' => 9.99,
            'currency' => 'USD',
            'period' => 'monthly',
            'included_credits' => 100,
            'status' => 'active',
        ]);

        $create->assertCreated()
            ->assertJsonPath('name', 'Starter');

        $list = $this->getJson('/api/v1/plans');
        $list->assertOk()
            ->assertJsonFragment(['name' => 'Starter']);
    }
}
