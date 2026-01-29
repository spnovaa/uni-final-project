<?php

namespace App\Services\Reporting;

use App\Models\Invoice;
use App\Models\WalletTransaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportingService implements ReportingServiceInterface
{
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
