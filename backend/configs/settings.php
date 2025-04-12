<?php

/** @beforeClass Config:  used by Config class */

return [
    "app" => [
        "name" => env('APP_NAME', null),
        "env" => env('APP_ENV', 'production'),
        "debug" => env('APP_DEBUG', false),
        "url" => env('APP_URL', 'http://localhost'),
        "timezone" => env('APP_TIMEZONE', 'UTC'),
        "locale" => env('APP_LOCALE', 'en'),
        "fallback_locale" => env('APP_FALLBACK_LOCALE', 'en'),
        "key" => env('APP_KEY', ''),
        "cipher" => env('APP_CIPHER', 'AES-256-CBC'),
    ],
    'database' => [
        "driver" => env('DB_CONNECTON', 'sqlite'),
        "host" => env('DB_HOST', 'database'),
        'file' => env('DB_FILE', 'db.sqlite'),
        "port" => env('DB_PORT', null),
        "database" => env('DB_NAME', null),
        "user" => env('DB_USER', null),
        "password" => env('DB_PASS', null),
        "charset" => env('DB_CHARSET', 'utf8'),
        "collation" => env('DB_COLLATION', 'utf8_unicode_ci'),
        "prefix" => env('DB_PREFIX', ''),
        'options' => [
            
        ],

    ],
    'mail' => [
        "driver" => env('MAIL_DRIVER', 'smtp'),
        "host" => env('MAIL_HOST', null),
        "port" => env('MAIL_PORT', null),
        "encryption" => env('MAIL_ENCRYPTION', 'tls'),
        "username" => env('MAIL_USERNAME', null),
        "password" => env('MAIL_PASSWORD', null),
        "from" => [
            "address" => env('MAIL_FROM_ADDRESS', null),
            "name" => env('MAIL_FROM_NAME', null),
        ],
    ],
    'session' => [
        "name" => env('SESSION_NAME', 'session'),
        "lifetime" => env('SESSION_LIFETIME', 120),
        "expire_on_close" => env('SESSION_EXPIRE_ON_CLOSE', false),
        "path" => env('SESSION_PATH', '/'),
        "domain" => env('SESSION_DOMAIN', null),
        "secure" => env('SESSION_SECURE', false),
        "httponly" => env('SESSION_HTTPONLY', true),
        "samesite" => env('SESSION_SAMESITE', 'Lax'),
    ],
    'cache' => [
        "driver" => env('CACHE_DRIVER', 'file'),
        "path" => env('CACHE_PATH', PATH_CACHE),
        "expire" => env('CACHE_EXPIRE', 60),
        "prefix" => env('CACHE_PREFIX', 'cache_'),
    ],
    'logging' => [
        "driver" => env('LOG_DRIVER', 'file'),
        "path" => env('LOG_PATH', PATH_LOGS),
        "level" => env('LOG_LEVEL', 'debug'),
    ],
    'queue' => [
        "driver" => env('QUEUE_DRIVER', 'sync'),
        "connection" => env('QUEUE_CONNECTION', 'default'),
        "queue" => env('QUEUE_NAME', 'default'),
        "retry_after" => env('QUEUE_RETRY_AFTER', 90),
    ],
    'filesystem' => [
        "default" => env('FILESYSTEM_DEFAULT', 'local'),
        "disks" => [
            "local" => [
                "driver" => "local",
                "root" => env('FILESYSTEM_LOCAL_ROOT', PATH_STORAGE . '/app'),
            ],
            "public" => [
                "driver" => "local",
                "root" => env('FILESYSTEM_PUBLIC_ROOT', PATH_STORAGE . '/app/public'),
                "url" => env('FILESYSTEM_PUBLIC_URL', '/storage'),
                "visibility" => 'public',
            ],
        ],
    ]
];
