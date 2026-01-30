<?php

namespace App\Domains\Gateway\Logging;

/**
 * Interface LogSinkInterface.
 */
interface LogSinkInterface
{
    /**
     * Send.
     * @param array $payload
     * @return void
     */
    public function send(array $payload): void;
}
