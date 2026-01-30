<?php

namespace App\Services\Reporting;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Reporting service contract.
 *
 * Exposes aggregated views of billing data (usage, wallet ledger, invoices).
 */
interface ReportingServiceInterface
{
    /**
     * Usage report.
     *
     * Implementations should aggregate usage within a date range and support grouping.
     * @param User $user
     * @param string $from
     * @param string $to
     * @param string $groupBy
     * @return Collection
     */
    public function usageReport(User $user, string $from, string $to, string $groupBy = 'day'): Collection;

    /**
     * Wallet ledger.
     *
     * Implementations should return wallet transactions within a date range.
     * @param User $user
     * @param string $from
     * @param string $to
     * @return Collection
     */
    public function walletLedger(User $user, string $from, string $to): Collection;

    /**
     * Invoices report.
     *
     * Implementations should return invoices and support filtering by status.
     * @param User $user
     * @param ?string $status
     * @return Collection
     */
    public function invoicesReport(User $user, ?string $status = null): Collection;
}
