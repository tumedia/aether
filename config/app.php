<?php

// THIS IS A SAMPLE CONFIG.

return [

    'env' => env('APP_ENV', 'production'),

    'cache' => [
        'enabled' => true,
        'class'   => AetherCacheMemcache::class,
        'options' => [],
    ],

    'sentry' => [
        'enabled' => false,
        'dsn' => '',
    ],

];
