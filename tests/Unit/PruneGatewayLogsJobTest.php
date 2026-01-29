<?php

namespace Tests\Unit;

use App\Jobs\PruneGatewayLogsJob;
use App\Models\AuditLog;
use App\Models\GatewayRequest;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneGatewayLogsJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_prunes_old_gateway_requests_and_audit_logs(): void
    {
        $oldDate = CarbonImmutable::now()->subDays(40);
        config([
            'gateway.log_retention_days' => 30,
            'gateway.audit_retention_days' => 30,
        ]);

        $request = GatewayRequest::query()->create([
            'endpoint' => 'chat.completions',
            'status' => '200',
        ]);
        $request->created_at = $oldDate;
        $request->updated_at = $oldDate;
        $request->save();

        AuditLog::query()->create([
            'action' => 'test.action',
            'created_at' => $oldDate,
        ]);

        (new PruneGatewayLogsJob())->handle();

        $this->assertDatabaseMissing('gateway_requests', [
            'endpoint' => 'chat.completions',
        ]);

        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'test.action',
        ]);
    }
}
