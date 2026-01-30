<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use App\Repositories\Audit\AuditLogRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Audit logging service.
 *
 * Records security-relevant events (key creation/revocation, subscription changes, etc.)
 * and provides filtered access for admin/reporting endpoints.
 */
class AuditLogService implements AuditLogServiceInterface
{
    /**
     * Create a new instance.
     * @param AuditLogRepositoryInterface $logs
     * @return void
     */
    public function __construct(private readonly AuditLogRepositoryInterface $logs)
    {
    }

    /**
     * Record an audit log entry for an action (optionally against a target model).
     * @param ?User $actor
     * @param string $action
     * @param ?Model $target
     * @param ?array $meta
     * @return AuditLog
     */
    public function record(
        ?User $actor,
        string $action,
        ?Model $target = null,
        ?array $meta = null
    ): AuditLog {
        return $this->logs->create([
            'actor_user_id' => $actor?->id,
            'action' => $action,
            'target_type' => $target ? $target::class : null,
            'target_id' => $target ? (string) $target->getKey() : null,
            'meta' => $meta,
            'created_at' => now(),
        ]);
    }

    /**
     * List audit logs with optional filters and a maximum limit.
     * @param array $filters
     * @param int $limit
     * @return Collection
     */
    public function list(array $filters = [], int $limit = 50): Collection
    {
        return $this->logs->list($filters, $limit);
    }
}
