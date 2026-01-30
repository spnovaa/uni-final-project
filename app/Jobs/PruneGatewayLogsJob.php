<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\GatewayRequest;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Prune old gateway request logs and audit logs based on configured retention.
 *
 * This job keeps the database size under control by deleting log rows older than
 * `gateway.log_retention_days` and `gateway.audit_retention_days`.
 */
class PruneGatewayLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Delete records past the configured retention windows.
     * @return void
     */
    public function handle(): void
    {
        $gatewayRetention = (int) config('gateway.log_retention_days', 30);
        $auditRetention = (int) config('gateway.audit_retention_days', 90);

        $gatewayCutoff = CarbonImmutable::now()->subDays($gatewayRetention);
        $auditCutoff = CarbonImmutable::now()->subDays($auditRetention);

        GatewayRequest::query()
            ->where('created_at', '<', $gatewayCutoff)
            ->delete();

        AuditLog::query()
            ->where('created_at', '<', $auditCutoff)
            ->delete();
    }
}
