<?php

namespace App\Repositories\Billing;

use App\Models\SubscriptionPlan;
use Illuminate\Support\Collection;

interface PlanRepositoryInterface
{
    public function listActive(): Collection;

    public function create(array $data): SubscriptionPlan;

    public function find(int $id): ?SubscriptionPlan;
}
