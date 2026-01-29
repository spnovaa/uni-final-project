<?php

namespace App\Domains\Gateway\DTOs;

class GatewayRequestDto
{
    public function __construct(
        public string $endpoint,
        public array $payload,
        public array $files = [],
        public ?string $provider = null,
        public ?string $model = null,
        public ?array $providerConfig = null,
    ) {
    }
}
