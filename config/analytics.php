<?php

use Dipantry\Analytics\RequestSessionProvider;

return [
    /*
     * Table name settings for analytics data
     */
    'table_prefix' => 'analytics_',

    /**
     * Determine if traffic from robots should be tracked.
     */
    'ignoreRobots' => false,

    /**
     * Mask.
     *
     * Mask routes so they are tracked together.
     */
    'mask' => [
        // '/users/*',
    ],

    'session' => [
        'provider' => RequestSessionProvider::class,
    ]
];