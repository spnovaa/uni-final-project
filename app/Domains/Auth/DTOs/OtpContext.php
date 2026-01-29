<?php

namespace App\Domains\Auth\DTOs;

use App\Models\OtpChallenge;
use App\Models\User;

class OtpContext
{
    public ?string $code = null;
    public ?OtpChallenge $challenge = null;
    public ?User $user = null;
    public array $errors = [];
    public int $status = 200;

    public function __construct(
        public string $destination,
        public string $channel,
        public ?string $ip = null,
    ) {
    }

    public function fail(string $message, int $status = 400): self
    {
        $this->errors[] = $message;
        $this->status = $status;

        return $this;
    }

    public function ok(): bool
    {
        return empty($this->errors);
    }
}
