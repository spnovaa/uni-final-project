<?php

namespace App\Domains\Gateway\Services;

use App\Domains\Gateway\DTOs\UsageMetrics;
use App\Models\ProviderModel;

/**
 * Service layer for usage metering.
 */
class UsageMeteringService
{
    /**
     * Build usage records.
     * @param ?UsageMetrics $usage
     * @param ?ProviderModel $providerModel
     * @return array
     */
    public function buildUsageRecords(?UsageMetrics $usage, ?ProviderModel $providerModel = null): array
    {
        if (! $usage) {
            return [];
        }

        $pricing = $providerModel?->pricing_config ?? [];
        $inputPer1k = (float) ($pricing['input_cost_per_1k'] ?? 0);
        $outputPer1k = (float) ($pricing['output_cost_per_1k'] ?? 0);
        $imageUnitCost = (float) ($pricing['image_cost_per_unit'] ?? 0);
        $audioSecondCost = (float) ($pricing['audio_cost_per_second'] ?? 0);
        $audioCharCost = (float) ($pricing['audio_cost_per_char'] ?? 0);

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

        if ($usage->images !== null) {
            $total = $imageUnitCost * $usage->images;
            $records[] = [
                'metric' => 'images',
                'quantity' => $usage->images,
                'unit_cost' => $imageUnitCost,
                'total_cost' => $total,
            ];
        }

        if ($usage->audioSeconds !== null) {
            $total = $audioSecondCost * $usage->audioSeconds;
            $records[] = [
                'metric' => 'audio_seconds',
                'quantity' => $usage->audioSeconds,
                'unit_cost' => $audioSecondCost,
                'total_cost' => $total,
            ];
        }

        if ($usage->audioCharacters !== null) {
            $total = $audioCharCost * $usage->audioCharacters;
            $records[] = [
                'metric' => 'audio_chars',
                'quantity' => $usage->audioCharacters,
                'unit_cost' => $audioCharCost,
                'total_cost' => $total,
            ];
        }

        return $records;
    }
}
