<?php

namespace App\Services\Reporting;

use App\Models\Invoice;
use App\Models\WalletTransaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reporting service for usage, wallet ledger, and invoices.
 *
 * Provides aggregated queries over billing tables so controllers return consistent report shapes.
 */
class ReportingService implements ReportingServiceInterface
{
    /**
     * Usage report.
     *
     * Aggregates usage records within a date range and groups by day, API key, or provider.
     * @param User $user
     * @param string $from
     * @param string $to
     * @param string $groupBy
     * @return Collection
     */
    public function usageReport(User $user, string $from, string $to, string $groupBy = 'day'): Collection
    {
        $fromDate = CarbonImmutable::parse($from)->startOfDay();
        $toDate = CarbonImmutable::parse($to)->endOfDay();

        $query = DB::table('usage_records')
            ->join('gateway_requests', 'usage_records.gateway_request_id', '=', 'gateway_requests.id')
            ->where('gateway_requests.user_id', $user->id)
            ->whereBetween('usage_records.created_at', [$fromDate, $toDate]);

        if ($groupBy === 'key') {
            $rows = $query
                ->selectRaw(
                    'gateway_requests.api_key_id as group_value, usage_records.metric as metric, SUM(usage_records.quantity) as quantity, SUM(usage_records.total_cost) as total_cost'
                )
                ->groupBy('gateway_requests.api_key_id', 'usage_records.metric')
                ->orderBy('group_value')
                ->get();
        } elseif ($groupBy === 'provider') {
            $rows = $query
                ->selectRaw(
                    'gateway_requests.provider_id as group_value, usage_records.metric as metric, SUM(usage_records.quantity) as quantity, SUM(usage_records.total_cost) as total_cost'
                )
                ->groupBy('gateway_requests.provider_id', 'usage_records.metric')
                ->orderBy('group_value')
                ->get();
        } else {
            $rows = $query
                ->selectRaw(
                    'CAST(usage_records.created_at as date) as group_value, usage_records.metric as metric, SUM(usage_records.quantity) as quantity, SUM(usage_records.total_cost) as total_cost'
                )
                ->groupBy(DB::raw('CAST(usage_records.created_at as date)'), 'usage_records.metric')
                ->orderBy('group_value')
                ->get();
        }

        return $rows->map(function ($row) use ($groupBy) {
            return [
                'group_by' => $groupBy,
                'group_value' => $row->group_value,
                'metric' => $row->metric,
                'quantity' => (float) $row->quantity,
                'total_cost' => (float) $row->total_cost,
            ];
        });
    }

    /**
     * Wallet ledger.
     *
     * Returns wallet transactions for the authenticated user within a date range.
     * @param User $user
     * @param string $from
     * @param string $to
     * @return Collection
     */
    public function walletLedger(User $user, string $from, string $to): Collection
    {
        $fromDate = CarbonImmutable::parse($from)->startOfDay();
        $toDate = CarbonImmutable::parse($to)->endOfDay();

        return WalletTransaction::query()
            ->whereHas('wallet', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Invoices report.
     *
     * Returns invoices for a user and optionally filters by invoice status.
     * @param User $user
     * @param ?string $status
     * @return Collection
     */
    public function invoicesReport(User $user, ?string $status = null): Collection
    {
        return Invoice::query()
            ->where('user_id', $user->id)
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByDesc('id')
            ->get();
    }
}
