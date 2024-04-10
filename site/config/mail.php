<?php

return [

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'verify_peer' => env('MAIL_VERIFY_PEER', false),
        ],

        'mailgun' => [
            'transport' => 'mailgun',
        ],
    ],

];
