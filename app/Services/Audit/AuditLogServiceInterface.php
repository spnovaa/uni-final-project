<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Service layer for audit log.
 */
interface AuditLogServiceInterface
{
    /**
     * Record Audit log.
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
     * List Audit logs.
     * @param array $filters
     * @param int $limit
     * @return Collection
     */
    public function list(array $filters = [], int $limit = 50): Collection;
}
