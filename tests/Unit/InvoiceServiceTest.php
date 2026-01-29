<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\Billing\Invoice\InvoiceServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_draft_builds_items_and_totals(): void
    {
        $user = User::factory()->create();
        $service = app(InvoiceServiceInterface::class);

        $invoice = $service->createDraft($user, [
            [
                'type' => 'usage',
                'description' => 'Chat usage',
                'quantity' => 10,
                'unit_price' => 0.5,
            ],
            [
                'type' => 'subscription',
                'description' => 'Monthly plan',
                'quantity' => 1,
                'unit_price' => 10,
            ],
        ], 'USD', 1.5);

        $this->assertSame('draft', $invoice->status);
        $this->assertCount(2, $invoice->items);
        $this->assertEquals(15.0, (float) $invoice->subtotal);
        $this->assertEquals(16.5, (float) $invoice->total);
    }
}
