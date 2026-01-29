<?php

namespace App\Domains\Gateway\DTOs;

class UsageMetrics
{
    public function __construct(
        public ?int $promptTokens = null,
        public ?int $completionTokens = null,
        public ?int $totalTokens = null,
    ) {
    }
}
