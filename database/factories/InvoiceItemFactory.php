<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 100);
        $unitPrice = $this->faker->randomFloat(4, 0.0001, 2);
        $lineTotal = $quantity * $unitPrice;

        return [
            'invoice_id' => Invoice::factory(),
            'type' => 'usage',
            'description' => $this->faker->sentence(4),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'meta' => ['source' => 'test'],
        ];
    }
}
