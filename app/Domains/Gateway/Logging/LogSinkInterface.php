<?php

namespace App\Domains\Gateway\Logging;

/**
 * Contract for external log sinks.
 *
 * Implementations send a structured gateway log payload to an external system (e.g. Loki,
 * Better Stack) without coupling the gateway to a single vendor.
 */
interface LogSinkInterface
{
    /**
     * Send a log payload to the sink.
     * @param array $payload
     * @return void
     */
    public function send(array $payload): void;
}
