<?php

namespace App\Repositories\Billing;

use App\Models\SubscriptionPlan;
use Illuminate\Support\Collection;

class PlanRepository implements PlanRepositoryInterface
{
    public function listActive(): Collection
    {
        return SubscriptionPlan::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get();
    }

    public function create(array $data): SubscriptionPlan
    {
        return SubscriptionPlan::query()->create($data);
    }

    public function find(int $id): ?SubscriptionPlan
    {
        return SubscriptionPlan::query()->find($id);
    }
}
