<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */
    

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'package_module' => [
        'base_url' => env('PACKAGE_MODULE_URL', 'http://localhost:8000/api/ws'),
        'api_key' => env('PACKAGE_MODULE_API_KEY', 'your-api-key-here'),
    ],
    
    'payment_module' => [
        'base_url' => env('PAYMENT_MODULE_URL', 'http://localhost:8000/api/ws'),
        'api_key' => env('PAYMENT_MODULE_API_KEY', 'your-api-key-here'),
    ],
    'modules' => [
        'notification' => [
            'api_key' => env('NOTIFICATION_MODULE_API_KEY'),
            'base_url' => env('NOTIFICATION_MODULE_URL', 'http://localhost:8000/api/v1/notification'),
        ],
        'driver' => [
            'api_key' => env('DRIVER_MODULE_API_KEY'),
            'base_url' => env('DRIVER_MODULE_URL', 'http://localhost:8000/api/v1/driver'),
        ],
        'feedback' => [
            'api_key' => env('FEEDBACK_MODULE_API_KEY'),
            'base_url' => env('FEEDBACK_MODULE_URL', 'http://localhost:8000/api/v1/feedback'),
        ],

    ],
        'internal_api_key' => env('INTERNAL_API_KEY'),
];
