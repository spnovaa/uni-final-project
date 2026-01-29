<?php

namespace Tests\Feature;

use App\Jobs\GenerateInvoicePdfJob;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_and_show_invoices(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
        ]);
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
        ]);

        Sanctum::actingAs($user);

        $list = $this->getJson('/api/v1/invoices');
        $list->assertOk()
            ->assertJsonFragment(['number' => $invoice->number]);

        $show = $this->getJson("/api/v1/invoices/{$invoice->id}");
        $show->assertOk()
            ->assertJsonStructure(['id', 'number', 'items', 'pdf_url']);
    }

    public function test_pdf_endpoint_dispatches_job_when_missing(): void
    {
        Queue::fake();
        Storage::fake('local');

        $invoice = Invoice::factory()->create();

        $signedUrl = URL::temporarySignedRoute(
            'invoices.pdf',
            now()->addMinutes(5),
            ['invoice' => $invoice->id]
        );

        $response = $this->get($signedUrl);

        $response->assertStatus(202)
            ->assertJson(['status' => 'processing']);

        Queue::assertPushed(GenerateInvoicePdfJob::class);
    }
}
