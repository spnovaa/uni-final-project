<?php

namespace App\Domains\Gateway\Logging;

class NullLogSink implements LogSinkInterface
{
    public function send(array $payload): void
    {
        // Intentionally no-op.
    }
}
