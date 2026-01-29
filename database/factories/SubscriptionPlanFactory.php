<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word().' Plan',
            'price' => $this->faker->randomFloat(2, 0, 99),
            'currency' => 'USD',
            'period' => 'monthly',
            'included_credits' => $this->faker->randomFloat(2, 0, 1000),
            'rate_limits' => ['rpm' => 60],
            'features' => ['priority_support' => false],
            'status' => 'active',
        ];
    }
}
