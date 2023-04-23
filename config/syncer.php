<?php

return [
    'servers' => [
        'current' => env('SYNCER_NAME', 'default'),
        'master' => env('SYNCER_MASTER', false),
        'slaves' => ['slave'],
    ],
    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'prefix' => '',
        ],

        'connection' => [
            'url' => env('REDIS_URL'),
            'host' => env('SYNCER_REDIS_HOST', '127.0.0.1'),
            'username' => env('SYNCER_REDIS_USERNAME'),
            'password' => env('SYNCER_REDIS_PASSWORD'),
            'port' => env('SYNCER_REDIS_PORT', '6379'),
            'database' => env('SYNCER_REDIS_DB', '0'),
        ],
    ],
];
