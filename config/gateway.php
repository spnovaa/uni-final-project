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
];
