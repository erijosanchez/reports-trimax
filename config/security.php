<?php

return [
    'login' => [
        'max_attempts' => env('LOGIN_MAX_ATTEMPTS', 5),
        'lockout_duration' => env('LOGIN_LOCKOUT_DURATION', 15),
        'max_hourly_attempts' => env('LOGIN_MAX_HOURLY_ATTEMPTS', 20),
    ],

    'ip_blacklist' => [
        'auto_block' => env('IP_AUTO_BLOCK', true),
        'auto_block_duration' => env('IP_AUTO_BLOCK_DURATION', 60),
        'whitelist' => [
            '127.0.0.1',
            '::1',
        ],
    ],

    'rate_limiting' => [
        'enabled' => env('RATE_LIMITING_ENABLED', true),
        'default_per_minute' => env('RATE_LIMIT_PER_MINUTE', 60),
    ],

    'session' => [
        'timeout' => env('SESSION_TIMEOUT', 3600),
        'track_location' => env('TRACK_USER_LOCATION', true),
    ],
];
