<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    /*
    |--------------------------------------------------------------------------
    | Bakong
    |--------------------------------------------------------------------------
    */

   'bakong' => [
    'base_url' => env(
        'BAKONG_BASE_URL',
        'https://api-bakong.nbc.gov.kh'
    ),

    'token' => env(
        'BAKONG_TOKEN'
    ),

    'name' => env(
        'BAKONG_NAME',
        'My Clothing POS'
    ),

    'app_icon_url' => env(
        'BAKONG_APP_ICON_URL'
    ),

    'callback' => env(
        'BAKONG_APP_CALLBACK'
    ),
],

];