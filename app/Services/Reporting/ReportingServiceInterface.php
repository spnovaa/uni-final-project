<?php

namespace App\Services\Reporting;

use App\Models\User;
use Illuminate\Support\Collection;

interface ReportingServiceInterface
{
    public function usageReport(User $user, string $from, string $to, string $groupBy = 'day'): Collection;

    public function walletLedger(User $user, string $from, string $to): Collection;

    public function invoicesReport(User $user, ?string $status = null): Collection;
}
