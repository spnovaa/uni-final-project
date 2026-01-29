<?php

namespace App\Domains\Gateway\Services;

use App\Domains\Gateway\DTOs\UsageMetrics;
use App\Models\ProviderModel;

class UsageMeteringService
{
    public function buildUsageRecords(?UsageMetrics $usage, ?ProviderModel $providerModel = null): array
    {
        if (! $usage) {
            return [];
        }

        $pricing = $providerModel?->pricing_config ?? [];
        $inputPer1k = (float) ($pricing['input_cost_per_1k'] ?? 0);
        $outputPer1k = (float) ($pricing['output_cost_per_1k'] ?? 0);

        $records = [];

        if ($usage->promptTokens !== null) {
            $unitCost = $inputPer1k / 1000;
            $total = $unitCost * $usage->promptTokens;
            $records[] = [
                'metric' => 'tokens_in',
                'quantity' => $usage->promptTokens,
                'unit_cost' => $unitCost,
                'total_cost' => $total,
            ];
        }

        if ($usage->completionTokens !== null) {
            $unitCost = $outputPer1k / 1000;
            $total = $unitCost * $usage->completionTokens;
            $records[] = [
                'metric' => 'tokens_out',
                'quantity' => $usage->completionTokens,
                'unit_cost' => $unitCost,
                'total_cost' => $total,
            ];
        }

        if ($usage->totalTokens !== null && empty($records)) {
            $records[] = [
                'metric' => 'tokens_total',
                'quantity' => $usage->totalTokens,
                'unit_cost' => 0,
                'total_cost' => 0,
            ];
        }

        return $records;
    }
}
