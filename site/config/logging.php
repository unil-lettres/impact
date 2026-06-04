<?php

return [

    'channels' => [
        'bugsnag' => [
            'driver' => 'bugsnag',
        ],

        'stack' => [
            'driver' => 'stack',
            'channels' => ['stderr', 'bugsnag'],
            'ignore_exceptions' => false,
        ],

        'dev' => [
            'driver' => 'stack',
            'channels' => ['stderr'],
            'ignore_exceptions' => false,
        ],
    ],

];
