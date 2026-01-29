<?php

return [
    'ttl_minutes' => (int) env('OTP_TTL_MINUTES', 10),
    'code_length' => (int) env('OTP_CODE_LENGTH', 6),
    'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
    'throttle_per_minute' => (int) env('OTP_THROTTLE_PER_MINUTE', 5),
];
