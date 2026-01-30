<?php

namespace App\Repositories\Audit;

use App\Models\AuditLog;
use Illuminate\Support\Collection;

/**
 * Repository for querying and persisting AuditLog records.
 */
class AuditLogRepository implements AuditLogRepositoryInterface
{
    /**
     * Create an audit log entry.
     * @param array $data
     * @return AuditLog
     */
    public function create(array $data): AuditLog
    {
        return AuditLog::query()->create($data);
    }

    /**
     * List audit logs with optional filters and a maximum limit.
     * @param array $filters
     * @param int $limit
     * @return Collection
     */
    public function list(array $filters = [], int $limit = 50): Collection
    {
        $query = AuditLog::query()->orderByDesc('id');

        if (! empty($filters['actor_user_id'])) {
            $query->where('actor_user_id', $filters['actor_user_id']);
        }

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['target_type'])) {
            $query->where('target_type', $filters['target_type']);
        }

        if (! empty($filters['target_id'])) {
            $query->where('target_id', $filters['target_id']);
        }

        return $query->limit($limit)->get();
    }
}
