<?php

namespace App\Services\Billing\Plan;

use App\Models\SubscriptionPlan;
use App\Repositories\Billing\PlanRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class PlanService implements PlanServiceInterface
{
    public function __construct(private readonly PlanRepositoryInterface $plans)
    {
    }

    public function listActive(): Collection
    {
        return Cache::remember('plans.active', 300, function () {
            return $this->plans->listActive();
        });
    }

    public function create(array $data): SubscriptionPlan
    {
        $plan = $this->plans->create($data);
        Cache::forget('plans.active');

        return $plan;
    }

    public function findOrFail(int $id): SubscriptionPlan
    {
        $plan = $this->plans->find($id);

        if (! $plan) {
            throw ValidationException::withMessages([
                'plan_id' => ['Plan not found.'],
            ]);
        }

        return $plan;
    }
}
