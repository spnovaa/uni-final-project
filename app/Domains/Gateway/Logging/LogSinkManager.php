<?php

namespace App\Domains\Gateway\Logging;

use InvalidArgumentException;

/**
 * Resolve log sink implementations by name.
 *
 * This manager enables switching between external log services by changing a single config value
 * (`gateway.log_sink`) without touching the gateway pipeline code.
 */
class LogSinkManager
{
    /**
     * Resolve the sink implementation for the given name (or from config when null).
     * @param ?string $name
     * @return LogSinkInterface
     */
    public function resolve(?string $name = null): LogSinkInterface
    {
        $name = $name ?: config('gateway.log_sink');

        return match ($name) {
            null, '', 'none' => app(NullLogSink::class),
            'loki' => app(LokiLogSink::class),
            'betterstack' => app(BetterStackLogSink::class),
            default => throw new InvalidArgumentException('Unknown log sink: '.$name),
        };
    }
}
