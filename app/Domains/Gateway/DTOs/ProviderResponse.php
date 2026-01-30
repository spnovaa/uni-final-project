<?php

namespace App\Domains\Gateway\DTOs;

/**
 * DTO for provider response.
 */
class ProviderResponse
{
    /**
     * Create a new instance.
     * @param int $status
     * @param array|string $body
     * @param array $headers
     * @param bool $isBinary
     * @return void
     */
    public function __construct(
        public int $status,
        public array|string $body,
        public array $headers = [],
        public bool $isBinary = false,
    ) {
    }
}
