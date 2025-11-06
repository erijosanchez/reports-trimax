<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */

    'rate_limiting' => [
        'enabled' => env('RATE_LIMITING_ENABLED', true),
        
        // Límites por defecto
        'default_per_minute' => env('RATE_LIMIT_PER_MINUTE', 60),
        
        // Límites por rol
        'roles' => [
            'super_admin' => null, // Sin límites
            'admin' => 200,
            'user' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Login Security Configuration
    |--------------------------------------------------------------------------
    */

    'login' => [
        // Máximo de intentos fallidos antes de bloquear
        'max_attempts' => env('LOGIN_MAX_ATTEMPTS', 5),
        
        // Duración del bloqueo en minutos
        'lockout_duration' => env('LOGIN_LOCKOUT_DURATION', 15),
        
        // Intentos por hora antes de bloqueo permanente
        'max_hourly_attempts' => env('LOGIN_MAX_HOURLY_ATTEMPTS', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Blacklist Configuration
    |--------------------------------------------------------------------------
    */

    'ip_blacklist' => [
        // Habilitar bloqueo automático
        'auto_block' => env('IP_AUTO_BLOCK', true),
        
        // Duración del bloqueo automático en minutos
        'auto_block_duration' => env('IP_AUTO_BLOCK_DURATION', 60),
        
        // IPs whitelisted (nunca bloquear)
        'whitelist' => [
            '127.0.0.1',
            '::1',
            // Agregar IPs de confianza aquí
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Logging Configuration
    |--------------------------------------------------------------------------
    */

    'activity_logging' => [
        'enabled' => env('ACTIVITY_LOGGING_ENABLED', true),
        
        // Eventos a registrar
        'events' => [
            'login',
            'logout',
            'failed_login',
            'password_reset',
            'user_created',
            'user_updated',
            'user_deleted',
            'file_upload',
            'file_download',
            'dashboard_view',
        ],
        
        // Retención de logs en días
        'retention_days' => env('LOG_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */

    'security_headers' => [
        'enabled' => env('SECURITY_HEADERS_ENABLED', true),
        
        'headers' => [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(self), microphone=()',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CORS Configuration
    |--------------------------------------------------------------------------
    */

    'cors' => [
        'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:8000')),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
        'allowed_headers' => ['*'],
        'max_age' => 3600,
    ],
];
