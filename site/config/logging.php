<?php

return [

    'channels' => [
        'bugsnag' => [
            'driver' => 'bugsnag',
        ],

        'dev' => [
            'driver' => 'stack',
            'channels' => ['daily'],
            'ignore_exceptions' => false,
        ],
    ],

];
