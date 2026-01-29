<?php

namespace App\Domains\Gateway\Services;

use App\Models\ProviderModel;
use App\Models\RoutingRule;
use Illuminate\Support\Facades\Schema;

class ProviderRouter
{
    public function resolveProviderModel(?string $modelKey): ?ProviderModel
    {
        if (empty($modelKey) || ! Schema::hasTable('provider_models')) {
            return null;
        }

        return ProviderModel::query()
            ->with('provider')
            ->where('model_key', $modelKey)
            ->where('status', 'active')
            ->first();
    }

    public function resolveRoutingRule(?string $modelKey): ?RoutingRule
    {
        if (! Schema::hasTable('routing_rules')) {
            return null;
        }

        return RoutingRule::query()
            ->where('status', 'active')
            ->first();
    }
}
