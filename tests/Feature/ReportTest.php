<?php

namespace Tests\Feature;

use App\Models\ApiClient;
use App\Models\ApiKey;
use App\Models\GatewayRequest;
use App\Models\Invoice;
use App\Models\Provider;
use App\Models\ProviderModel;
use App\Models\UsageRecord;
use App\Models\User;
use App\Services\Billing\Wallet\WalletServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_usage_wallet_and_invoice_reports(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $client = ApiClient::query()->create([
            'user_id' => $user->id,
            'name' => 'Reporting Client',
            'status' => 'active',
        ]);

        $apiKey = ApiKey::query()->create([
            'api_client_id' => $client->id,
            'key_prefix' => 'testkey',
            'key_hash' => hash_hmac('sha256', 'raw-key', config('app.key')),
        ]);

        $provider = Provider::query()->create([
            'name' => 'test-provider',
            'type' => 'openai_compatible',
            'base_url' => 'https://example.com',
            'status' => 'active',
            'priority' => 0,
        ]);

        $providerModel = ProviderModel::query()->create([
            'provider_id' => $provider->id,
            'model_key' => 'gpt-test',
            'status' => 'active',
        ]);

        $request = GatewayRequest::query()->create([
            'api_key_id' => $apiKey->id,
            'user_id' => $user->id,
            'provider_id' => $provider->id,
            'provider_model_id' => $providerModel->id,
            'endpoint' => 'chat.completions',
            'status' => '200',
        ]);

        UsageRecord::query()->create([
            'gateway_request_id' => $request->id,
            'metric' => 'tokens_in',
            'quantity' => 100,
            'unit_cost' => 0.0001,
            'total_cost' => 0.01,
        ]);

        $wallets = app(WalletServiceInterface::class);
        $wallets->topup($user, 5, 'test_topup');

        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'status' => 'issued',
        ]);

        $usage = $this->getJson('/api/v1/reports/usage?group_by=day');
        $usage->assertOk()
            ->assertJsonFragment(['metric' => 'tokens_in']);

        $ledger = $this->getJson('/api/v1/reports/wallet-ledger');
        $ledger->assertOk()
            ->assertJsonFragment(['reason' => 'test_topup']);

        $invoices = $this->getJson('/api/v1/reports/invoices?status=issued');
        $invoices->assertOk()
            ->assertJsonFragment(['number' => $invoice->number]);
    }
}
