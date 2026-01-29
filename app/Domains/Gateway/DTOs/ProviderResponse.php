<?php

namespace App\Domains\Gateway\DTOs;

class ProviderResponse
{
    public function __construct(
        public int $status,
        public array|string $body,
        public array $headers = [],
        public bool $isBinary = false,
    ) {
    }
}
