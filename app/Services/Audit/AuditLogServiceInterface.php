<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface AuditLogServiceInterface
{
    public function record(
        ?User $actor,
        string $action,
        ?Model $target = null,
        ?array $meta = null
    ): AuditLog;

    public function list(array $filters = [], int $limit = 50): Collection;
}
