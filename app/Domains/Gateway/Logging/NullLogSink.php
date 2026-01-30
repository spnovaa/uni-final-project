<?php

namespace App\Domains\Gateway\Logging;

/**
 * No-op log sink used when external logging is disabled.
 */
class NullLogSink implements LogSinkInterface
{
    /**
     * Ignore the payload.
     * @param array $payload
     * @return void
     */
    public function send(array $payload): void
    {
        // Intentionally no-op.
    }
}
