<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application constants
    |--------------------------------------------------------------------------
    */

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
        ],
    ],

    'users' => [
        'validity' => env('USER_VALIDITY', 12),
    ],

];
