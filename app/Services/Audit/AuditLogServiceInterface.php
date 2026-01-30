<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Audit logging service contract.
 *
 * Implementations should persist structured audit events and support filtered listing.
 */
interface AuditLogServiceInterface
{
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
    ): AuditLog;

    /**
     * List audit logs with optional filters and a maximum limit.
     * @param array $filters
     * @param int $limit
     * @return Collection
     */
    public function list(array $filters = [], int $limit = 50): Collection;
}
