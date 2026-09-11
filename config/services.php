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
        'region' => env(
            'AWS_DEFAULT_REGION',
            'us-east-1'
        ),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env(
                'SLACK_BOT_USER_OAUTH_TOKEN'
            ),
            'channel' => env(
                'SLACK_BOT_USER_DEFAULT_CHANNEL'
            ),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Bakong
    |--------------------------------------------------------------------------
    */

    'bakong' => [

        // Bakong API
        'base_url' => env(
            'BAKONG_BASE_URL',
            'https://api-bakong.nbc.gov.kh'
        ),

        // Bakong API access token
        'token' => env(
            'BAKONG_TOKEN'
        ),

        // REAL Bakong merchant account
        'account_id' => env(
            'BAKONG_ACCOUNT_ID'
        ),

        // Merchant information
        'merchant_name' => env(
            'BAKONG_MERCHANT_NAME',
            'My Clothing POS'
        ),

        'merchant_city' => env(
            'BAKONG_MERCHANT_CITY',
            'PHNOM PENH'
        ),

        // Currency
        'currency' => env(
            'BAKONG_CURRENCY',
            'USD'
        ),

        // KHQR information
        'store_label' => env(
            'BAKONG_STORE_LABEL',
            'My Clothing POS'
        ),

        'terminal_label' => env(
            'BAKONG_TERMINAL_LABEL',
            'Online Store'
        ),

        'purpose' => env(
            'BAKONG_PURPOSE',
            'Clothing order payment'
        ),

        // Optional application information
        'app_icon_url' => env(
            'BAKONG_APP_ICON_URL'
        ),

        'app_name' => env(
            'BAKONG_APP_NAME',
            'My Clothing POS'
        ),

        'callback' => env(
            'BAKONG_CALLBACK'
        ),
    ],

];