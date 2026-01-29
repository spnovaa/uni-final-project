<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\Audit\AuditLogServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_audit_log(): void
    {
        $user = User::factory()->create();

        $service = app(AuditLogServiceInterface::class);
        $log = $service->record($user, 'test.action', null, ['foo' => 'bar']);

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'actor_user_id' => $user->id,
            'action' => 'test.action',
        ]);
    }
}
