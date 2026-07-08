<?php

return [
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => (int) env('REDIS_PORT', 6379),
        'password' => env('REDIS_PASSWORD', null),
        'database' => (int) env('REDIS_DATABASE', 0),
        'prefix' => env('REDIS_PREFIX', 'webman_saas:'),
        'timeout' => (float) env('REDIS_TIMEOUT', 2.0),
    ],
];
