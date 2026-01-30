<?php

namespace App\Jobs;

use App\Domains\Gateway\Services\ProviderRegistry;
use App\Models\Provider;
use App\Models\ProviderHealthCheck;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Queued job for provider health check.
 */
class ProviderHealthCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new instance.
     * @param ?int $providerId
     * @return void
     */
    public function __construct(public ?int $providerId = null)
    {
    }

    /**
     * Handle the queued job.
     * @param ProviderRegistry $registry
     * @return void
     */
    public function handle(ProviderRegistry $registry): void
    {
        $providers = Provider::query()
            ->where('status', 'active')
            ->when($this->providerId, function ($query) {
                $query->where('id', $this->providerId);
            })
            ->get();

        foreach ($providers as $provider) {
            $config = $registry->getProviderConfig($provider->name);
            $baseUrl = rtrim($config['base_url'] ?? $provider->base_url ?? '', '/');

            if (! $baseUrl) {
                $this->record($provider, 'unknown', null, [
                    'error' => 'Missing base URL',
                ]);
                continue;
            }

            $path = $provider->type === 'openai_compatible' ? '/models' : '/';
            $client = Http::timeout(10);

            if (! empty($config['api_key'])) {
                $client = $client->withToken($config['api_key']);
            }

            $start = microtime(true);

            try {
                $response = $client->get($baseUrl.$path);
                $latency = (int) round((microtime(true) - $start) * 1000);
                $status = $response->successful() ? 'up' : 'down';

                $this->record($provider, $status, $latency, [
                    'http_status' => $response->status(),
                ]);
            } catch (ConnectionException $exception) {
                $this->record($provider, 'down', null, [
                    'error' => $exception->getMessage(),
                ]);
            } catch (Throwable $exception) {
                $this->record($provider, 'down', null, [
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * Record.
     * @param Provider $provider
     * @param string $status
     * @param ?int $latency
     * @param array $meta
     * @return void
     */
    private function record(Provider $provider, string $status, ?int $latency, array $meta): void
    {
        ProviderHealthCheck::query()->create([
            'provider_id' => $provider->id,
            'status' => $status,
            'latency_ms' => $latency,
            'checked_at' => now(),
            'meta' => $meta,
        ]);
    }
}
