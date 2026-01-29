<?php

namespace App\Pipelines\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Services\ProviderRegistry;
use App\Domains\Gateway\Services\ProviderRouter;
use Closure;

class SelectProviderPipe
{
    public function __construct(
        private readonly ProviderRegistry $registry,
        private readonly ProviderRouter $router,
    ) {
    }

    public function handle(GatewayRequestContext $context, Closure $next)
    {
        $providerName = $context->providerName;

        if ($context->modelKey) {
            $providerModel = $this->router->resolveProviderModel($context->modelKey);
            $context->providerModel = $providerModel;

            if (! $providerName && $providerModel?->provider) {
                $providerName = $providerModel->provider->name;
            }
        }

        $providerName = $providerName ?: config('gateway.default_provider');
        $context->providerName = $providerName;
        $context->providerConfig = $this->registry->getProviderConfig($providerName);
        $context->providerId = $context->providerConfig['provider_id'] ?? ($context->providerModel?->provider_id);

        return $next($context);
    }
}
