<?php

namespace App\Domains\Gateway\Logging;

/**
 * Class NullLogSink.
 */
class NullLogSink implements LogSinkInterface
{
    /**
     * Send.
     * @param array $payload
     * @return void
     */
    public function send(array $payload): void
    {
        // Intentionally no-op.
    }
}
