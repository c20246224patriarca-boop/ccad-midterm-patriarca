
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Municipal Bus Terminal System
    |--------------------------------------------------------------------------
    */

    'bus_terminal' => [
        'name' => env(
            'BUS_TERMINAL_NAME',
            'Municipal Bus Terminal'
        ),

        'location' => env(
            'BUS_TERMINAL_LOCATION',
            'Municipal Terminal'
        ),

        'contact' => env(
            'BUS_TERMINAL_CONTACT',
            ''
        ),

        'email' => env(
            'BUS_TERMINAL_EMAIL',
            ''
        ),
    ],

];
