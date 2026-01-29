<?php

return [
    'default_provider' => env('GATEWAY_DEFAULT_PROVIDER', 'openai'),

    'providers' => [
        'openai' => [
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'api_key' => env('OPENAI_API_KEY'),
            'timeout' => (int) env('OPENAI_TIMEOUT', 60),
        ],
    ],

    'persist_logs' => env('GATEWAY_PERSIST_LOGS', true),
    'rate_limit_per_min' => (int) env('GATEWAY_RATE_LIMIT_PER_MIN', 60),
    'require_wallet' => env('GATEWAY_REQUIRE_WALLET', true),
    'log_retention_days' => (int) env('GATEWAY_LOG_RETENTION_DAYS', 30),
    'audit_retention_days' => (int) env('GATEWAY_AUDIT_RETENTION_DAYS', 90),
    'token_chars_per_token' => (int) env('GATEWAY_TOKEN_CHARS_PER_TOKEN', 4),
];
