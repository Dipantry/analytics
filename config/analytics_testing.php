<?php

use Dipantry\Analytics\RequestSessionProvider;

return [
    'table_prefix' => 'analytics_',
    'ignoreRobots' => false,
    'mask' => [],
    'session' => [
        'provider' => RequestSessionProvider::class,
    ]
];