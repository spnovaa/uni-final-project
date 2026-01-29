<?php

namespace App\Services\Billing\Plan;

use App\Models\SubscriptionPlan;
use Illuminate\Support\Collection;

interface PlanServiceInterface
{
    public function listActive(): Collection;

    public function create(array $data): SubscriptionPlan;

    public function findOrFail(int $id): SubscriptionPlan;
}
