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
            'gemini' => GeminiProviderAdapter::class,
        ];

        if (isset($adapters[$providerName])) {
            $adapter = app($adapters[$providerName]);
            if ($adapter->supports($endpoint, $model)) {
                return $adapter;
            }

            throw new RuntimeException('Provider does not support this endpoint.');
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
