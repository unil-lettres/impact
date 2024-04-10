<?php

return [

    'channels' => [
        'bugsnag' => [
            'driver' => 'bugsnag',
        ],

        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'bugsnag'],
            'ignore_exceptions' => false,
        ],

        'dev' => [
            'driver' => 'stack',
            'channels' => ['daily'],
            'ignore_exceptions' => false,
        ],
    ],

];
