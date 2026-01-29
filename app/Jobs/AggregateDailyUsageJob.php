<?php

namespace App\Jobs;

use App\Models\DailyUsageRollup;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AggregateDailyUsageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?string $date = null)
    {
    }

    public function handle(): void
    {
        $targetDate = CarbonImmutable::parse($this->date ?? now()->subDay()->toDateString());
        $from = $targetDate->startOfDay();
        $to = $targetDate->endOfDay();

        $rows = DB::table('usage_records')
            ->join('gateway_requests', 'usage_records.gateway_request_id', '=', 'gateway_requests.id')
            ->whereBetween('usage_records.created_at', [$from, $to])
            ->selectRaw(
                'CAST(usage_records.created_at as date) as date,
                 gateway_requests.user_id as user_id,
                 gateway_requests.api_key_id as api_key_id,
                 gateway_requests.provider_id as provider_id,
                 gateway_requests.provider_model_id as provider_model_id,
                 usage_records.metric as metric,
                 SUM(usage_records.quantity) as quantity,
                 SUM(usage_records.total_cost) as total_cost'
            )
            ->groupBy(
                DB::raw('CAST(usage_records.created_at as date)'),
                'gateway_requests.user_id',
                'gateway_requests.api_key_id',
                'gateway_requests.provider_id',
                'gateway_requests.provider_model_id',
                'usage_records.metric'
            )
            ->get();

        foreach ($rows as $row) {
            DailyUsageRollup::query()->updateOrCreate([
                'date' => $row->date,
                'user_id' => $row->user_id,
                'api_key_id' => $row->api_key_id,
                'provider_id' => $row->provider_id,
                'provider_model_id' => $row->provider_model_id,
                'metric' => $row->metric,
            ], [
                'quantity' => $row->quantity,
                'total_cost' => $row->total_cost,
            ]);
        }
    }
}
