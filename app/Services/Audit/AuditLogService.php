<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use App\Repositories\Audit\AuditLogRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AuditLogService implements AuditLogServiceInterface
{
    public function __construct(private readonly AuditLogRepositoryInterface $logs)
    {
    }

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

    public function list(array $filters = [], int $limit = 50): Collection
    {
        return $this->logs->list($filters, $limit);
    }
}
