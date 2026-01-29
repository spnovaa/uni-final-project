<?php

return [
    'default_provider' => env('GATEWAY_DEFAULT_PROVIDER', 'openai'),

    'providers' => [
        'openai' => [
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'api_key' => env('OPENAI_API_KEY'),
            'timeout' => (int) env('OPENAI_TIMEOUT', 60),
        ],
        'gemini' => [
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
            'api_key' => env('GEMINI_API_KEY'),
            'timeout' => (int) env('GEMINI_TIMEOUT', 60),
        ],
        'groq' => [
            'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
            'api_key' => env('GROQ_API_KEY'),
            'timeout' => (int) env('GROQ_TIMEOUT', 60),
        ],
        'openrouter' => [
            'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
            'api_key' => env('OPENROUTER_API_KEY'),
            'timeout' => (int) env('OPENROUTER_TIMEOUT', 60),
        ],
    ],

    'persist_logs' => env('GATEWAY_PERSIST_LOGS', true),
    'rate_limit_per_min' => (int) env('GATEWAY_RATE_LIMIT_PER_MIN', 60),
    'require_wallet' => env('GATEWAY_REQUIRE_WALLET', true),
    'log_retention_days' => (int) env('GATEWAY_LOG_RETENTION_DAYS', 30),
    'audit_retention_days' => (int) env('GATEWAY_AUDIT_RETENTION_DAYS', 90),
    'tokenizer_default_encoding' => env('GATEWAY_TOKENIZER_DEFAULT_ENCODING', 'cl100k_base'),
    'audio_bytes_per_second' => (int) env('GATEWAY_AUDIO_BYTES_PER_SECOND', 16000),
];
