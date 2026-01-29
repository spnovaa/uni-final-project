<?php

namespace App\Repositories\Audit;

use App\Models\AuditLog;
use Illuminate\Support\Collection;

interface AuditLogRepositoryInterface
{
    public function create(array $data): AuditLog;

    public function list(array $filters = [], int $limit = 50): Collection;
}
