<?php

namespace App\Repositories\Audit;

use App\Models\AuditLog;
use Illuminate\Support\Collection;

/**
 * Repository contract for querying and persisting AuditLog records.
 */
interface AuditLogRepositoryInterface
{
    /**
     * Create an audit log entry.
     * @param array $data
     * @return AuditLog
     */
    public function create(array $data): AuditLog;

    /**
     * List audit logs with optional filters and a maximum limit.
     * @param array $filters
     * @param int $limit
     * @return Collection
     */
    public function list(array $filters = [], int $limit = 50): Collection;
}
