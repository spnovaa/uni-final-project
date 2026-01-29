<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_logs_created_for_key_and_subscription(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $clientResponse = $this->postJson('/api/v1/api-clients', [
            'name' => 'Primary Client',
        ])->assertCreated();

        $clientId = $clientResponse->json('id');

        $this->postJson("/api/v1/api-clients/{$clientId}/keys", [
            'scopes' => ['ai:chat'],
        ])->assertCreated();

        $plan = SubscriptionPlan::factory()->create([
            'price' => 0,
            'status' => 'active',
        ]);

        $this->postJson('/api/v1/subscriptions', [
            'plan_id' => $plan->id,
        ])->assertCreated();

        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $user->id,
            'action' => 'api_client.created',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $user->id,
            'action' => 'api_key.created',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $user->id,
            'action' => 'subscription.created',
        ]);
    }

    public function test_admin_can_list_audit_logs(): void
    {
        $admin = User::factory()->create();
        $role = Role::query()->create([
            'name' => 'admin',
        ]);

        $admin->roles()->attach($role);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/audit-logs');

        $response->assertOk();
    }
}
