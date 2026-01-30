<?php

namespace App\Services\Reporting;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Service layer for reporting.
 */
interface ReportingServiceInterface
{
    /**
     * Usage report.
     * @param User $user
     * @param string $from
     * @param string $to
     * @param string $groupBy
     * @return Collection
     */
    public function usageReport(User $user, string $from, string $to, string $groupBy = 'day'): Collection;

    /**
     * Wallet ledger.
     * @param User $user
     * @param string $from
     * @param string $to
     * @return Collection
     */
    public function walletLedger(User $user, string $from, string $to): Collection;

    /**
     * Invoices report.
     * @param User $user
     * @param ?string $status
     * @return Collection
     */
    public function invoicesReport(User $user, ?string $status = null): Collection;
}
