<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Connectors\SqlServerConnector;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\SqlServerConnection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use PDO;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (! config('database.default')) {
            config(['database.default' => env('DB_CONNECTION', 'sqlsrv')]);
        }

        $this->app->bind(\App\Repositories\Billing\WalletRepositoryInterface::class, \App\Repositories\Billing\WalletRepository::class);
        $this->app->bind(\App\Repositories\Billing\PlanRepositoryInterface::class, \App\Repositories\Billing\PlanRepository::class);
        $this->app->bind(\App\Repositories\Billing\SubscriptionRepositoryInterface::class, \App\Repositories\Billing\SubscriptionRepository::class);
        $this->app->bind(\App\Repositories\Gateway\ProviderRepositoryInterface::class, \App\Repositories\Gateway\ProviderRepository::class);
        $this->app->bind(\App\Repositories\Gateway\ProviderModelRepositoryInterface::class, \App\Repositories\Gateway\ProviderModelRepository::class);

        $this->app->bind(\App\Services\Billing\Wallet\WalletServiceInterface::class, \App\Services\Billing\Wallet\WalletService::class);
        $this->app->bind(\App\Services\Billing\Plan\PlanServiceInterface::class, \App\Services\Billing\Plan\PlanService::class);
        $this->app->bind(\App\Services\Billing\Subscription\SubscriptionServiceInterface::class, \App\Services\Billing\Subscription\SubscriptionService::class);
        $this->app->bind(\App\Services\Gateway\Provider\ProviderServiceInterface::class, \App\Services\Gateway\Provider\ProviderService::class);
        $this->app->bind(\App\Services\Gateway\ProviderModel\ProviderModelServiceInterface::class, \App\Services\Gateway\ProviderModel\ProviderModelService::class);

        $this->app->resolving('db', function (DatabaseManager $db) {
            $db->extend('sqlsrv', function (array $config, ?string $name = null) {
                $connector = new SqlServerConnector();
                $connector->setDefaultOptions([]);

                $pdo = $connector->connect($config);

                return new SqlServerConnection($pdo, $config['database'], $config['prefix'] ?? '', $config);
            });
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
