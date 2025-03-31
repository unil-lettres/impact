<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application constants
    |--------------------------------------------------------------------------
    */

    'app_url' => env('APP_URL', 'http://impact.lan:8025'),

    'pagination' => [
        'per' => env('PAGINATION_PER', 25),
    ],

    'files' => [
        'video' => [
            'extension' => env('VIDEO_EXTENSION', 'mp4'),
            'width' => env('VIDEO_WIDTH', 640),
            'height' => env('VIDEO_HEIGHT', 480),
        ],
        'audio' => [
            'extension' => env('AUDIO_EXTENSION', 'mp3'),
        ],
        'ffmpeg' => [
            'timeout' => env('FILE_TIMEOUT', 3600),
            'progress' => [
                'update' => env('FILE_UPDATE', 10),
            ],
        ],
    ],

    'users' => [
        'validity' => env('USER_VALIDITY', 12),
        'account' => [
            'expiring' => env('USER_ACCOUNT_EXPIRING', 15),
        ],
    ],

    'moodle' => [
        'base' => env('MOODLE_BASE', null),
        'course' => env('MOODLE_COURSE', null),
        'api' => env('MOODLE_API', null),
        'token' => env('MOODLE_TOKEN', null),
        'sync' => [
            'timeout' => env('MOODLE_SYNC_TIMEOUT', 600),
        ],
    ],

    'switch' => [
        'user' => env('SWITCH_USER', null),
        'password' => env('SWITCH_PASSWORD', null),
        'endpoint' => env('SWITCH_ENDPOINT', 'https://eduid.ch/api'),
        'api_version' => env('SWITCH_API_VERSION', 'v1'),
    ],

];
