<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 5, 200);

        return [
            'user_id' => User::factory(),
            'number' => 'INV-'.$this->faker->unique()->numerify('########'),
            'status' => 'issued',
            'currency' => 'USD',
            'subtotal' => $subtotal,
            'tax' => 0,
            'total' => $subtotal,
            'issued_at' => now(),
        ];
    }
}
