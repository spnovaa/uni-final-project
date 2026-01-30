<?php

namespace App\Domains\Providers;

use RuntimeException;

/**
 * Resolve the correct provider adapter for a request.
 *
 * Maps provider names to adapter implementations and ensures the adapter supports the requested
 * endpoint/model combination. This enables switching providers by sending `provider` + `model`
 * in the OpenAI-compatible request payload.
 */
class ProviderAdapterResolver
{
    /**
     * Resolve an adapter for the given provider name and endpoint.
     *
     * Throws a RuntimeException when the provider is unknown or does not support the endpoint.
     *
     * @return ProviderAdapterInterface
     */
    public function resolve(string $providerName, string $endpoint, ?string $model = null): ProviderAdapterInterface
    {
        $adapters = [
            'openai' => OpenAiProviderAdapter::class,
            'gemini' => GeminiProviderAdapter::class,
            'groq' => OpenAiProviderAdapter::class,
            'openrouter' => OpenAiProviderAdapter::class,
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
