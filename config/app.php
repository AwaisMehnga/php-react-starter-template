<?php

/**
 * Application Configuration
 */

return [
    'name' => $_ENV['APP_NAME'] ?? 'Tool Site',
    'env' => $_ENV['APP_ENV'] ?? 'prod',
    'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',

    // Timezone
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',

    // Paths
    'paths' => [
        'views' => __DIR__ . '/../views',
        'uploads' => __DIR__ . '/../uploads',
        'logs' => __DIR__ . '/../logs',
    ],

    // React/Frontend
    'frontend' => [
        'dev_server' => $_ENV['VITE_DEV_SERVER'] ?? 'http://localhost:3000',
        'build_path' => '/build',
    ],
];
