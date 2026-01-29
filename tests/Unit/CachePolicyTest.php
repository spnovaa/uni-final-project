<?php

namespace Tests\Unit;

use App\Models\Provider;
use App\Services\Billing\Plan\PlanServiceInterface;
use App\Services\Gateway\Provider\ProviderServiceInterface;
use App\Services\Gateway\ProviderModel\ProviderModelServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CachePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_list_is_cached_and_invalidated(): void
    {
        config(['cache.default' => 'array']);
        Cache::store('array')->flush();

        $planService = app(PlanServiceInterface::class);

        \App\Models\SubscriptionPlan::factory()->create(['status' => 'active']);

        $planService->listActive();

        $this->assertTrue(Cache::has('plans:active'));

        $planService->create([
            'name' => 'Starter',
            'price' => 10,
            'currency' => 'USD',
            'period' => 'monthly',
            'status' => 'active',
        ]);

        $this->assertFalse(Cache::has('plans:active'));
    }

    public function test_provider_list_and_config_cache_are_invalidated(): void
    {
        config(['cache.default' => 'array']);
        Cache::store('array')->flush();

        $providerService = app(ProviderServiceInterface::class);

        Provider::query()->create([
            'name' => 'openai',
            'type' => 'openai_compatible',
            'base_url' => 'https://api.openai.com/v1',
            'status' => 'active',
            'priority' => 0,
        ]);

        $providerService->list();

        $this->assertTrue(Cache::has('providers:all'));

        Cache::put('providers:config:gemini', ['name' => 'gemini'], 300);

        $providerService->create([
            'name' => 'gemini',
            'type' => 'gemini',
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'status' => 'active',
            'priority' => 0,
            'api_key' => 'test-key',
        ]);

        $this->assertFalse(Cache::has('providers:all'));
        $this->assertFalse(Cache::has('providers:config:gemini'));
    }

    public function test_provider_model_list_cache_is_invalidated(): void
    {
        config(['cache.default' => 'array']);
        Cache::store('array')->flush();

        $provider = Provider::query()->create([
            'name' => 'openai',
            'type' => 'openai_compatible',
            'base_url' => 'https://api.openai.com/v1',
            'status' => 'active',
            'priority' => 0,
        ]);

        $service = app(ProviderModelServiceInterface::class);

        $service->listByProvider($provider->id);

        $this->assertTrue(Cache::has('provider_models:list:'.$provider->id));

        Cache::put('provider_models:openai:gpt-4o-mini', ['model' => 'gpt-4o-mini'], 300);

        $service->create([
            'provider_id' => $provider->id,
            'model_key' => 'gpt-4o-mini',
            'status' => 'active',
            'pricing_config' => [
                'input_cost_per_1k' => 0.5,
                'output_cost_per_1k' => 1.0,
            ],
        ]);

        $this->assertFalse(Cache::has('provider_models:list:'.$provider->id));
        $this->assertFalse(Cache::has('provider_models:openai:gpt-4o-mini'));
    }
}
