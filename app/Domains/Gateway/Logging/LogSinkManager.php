<?php

namespace App\Domains\Gateway\Logging;

use InvalidArgumentException;

class LogSinkManager
{
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
