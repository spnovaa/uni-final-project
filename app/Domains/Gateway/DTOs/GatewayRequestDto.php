<?php

namespace App\Domains\Gateway\DTOs;

/**
 * DTO for gateway request dto.
 */
class GatewayRequestDto
{
    /**
     * Create a new instance.
     * @param string $endpoint
     * @param array $payload
     * @param array $files
     * @param ?string $provider
     * @param ?string $model
     * @param ?array $providerConfig
     * @return void
     */
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
