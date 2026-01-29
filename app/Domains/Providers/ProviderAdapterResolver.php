<?php

namespace App\Domains\Providers;

use RuntimeException;

class ProviderAdapterResolver
{
    /**
     * @return ProviderAdapterInterface
     */
    public function resolve(string $providerName, string $endpoint, ?string $model = null): ProviderAdapterInterface
    {
        $adapters = [
            'openai' => OpenAiProviderAdapter::class,
        ];

        if (isset($adapters[$providerName])) {
            return app($adapters[$providerName]);
        }

        foreach ($adapters as $adapterClass) {
            $adapter = app($adapterClass);
            if ($adapter->supports($endpoint, $model)) {
                return $adapter;
            }
        }

        throw new RuntimeException('No provider adapter available.');
    }
}
