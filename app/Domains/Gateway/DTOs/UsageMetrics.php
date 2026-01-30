<?php

namespace App\Domains\Gateway\DTOs;

/**
 * DTO for usage metrics.
 */
class UsageMetrics
{
    /**
     * Create a new instance.
     * @param ?int $promptTokens
     * @param ?int $completionTokens
     * @param ?int $totalTokens
     * @param ?int $images
     * @param ?float $audioSeconds
     * @param ?int $audioCharacters
     * @return void
     */
    public function __construct(
        public ?int $promptTokens = null,
        public ?int $completionTokens = null,
        public ?int $totalTokens = null,
        public ?int $images = null,
        public ?float $audioSeconds = null,
        public ?int $audioCharacters = null,
    ) {
    }
}
