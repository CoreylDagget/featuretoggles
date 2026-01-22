<?php

return [
    'environment' => env('APP_ENV', 'prod'),

    'defaults' => [
        // 'checkout.new_flow' => false,
    ],

    'cache' => [
        'enabled' => true,
        'ttl_seconds' => 60,
        'use_env_snapshot' => true,
        'store' => env('FEATURE_TOGGLES_CACHE_STORE', null),
        'prefix' => 'feature_toggles:',
    ],

    'unknown_feature_behavior' => 'exception', // exception|false|true
    'db_failure_behavior' => 'defaults',       // defaults|false|true
];
