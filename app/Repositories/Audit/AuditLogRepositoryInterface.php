<?php

namespace App\Repositories\Audit;

use App\Models\AuditLog;
use Illuminate\Support\Collection;

/**
 * Persistence layer for audit log.
 */
interface AuditLogRepositoryInterface
{
    /**
     * Create.
     * @param array $data
     * @return AuditLog
     */
    public function create(array $data): AuditLog;

    /**
     * List.
     * @param array $filters
     * @param int $limit
     * @return Collection
     */
    public function list(array $filters = [], int $limit = 50): Collection;
}
