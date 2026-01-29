<?php

namespace App\Domains\Gateway\Logging;

interface LogSinkInterface
{
    public function send(array $payload): void;
}
