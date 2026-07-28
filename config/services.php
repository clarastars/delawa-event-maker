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

    'authentica' => [
        'api_key' => env('AUTHENTICA_API_KEY'),
        'base_url' => env('AUTHENTICA_BASE_URL', 'https://api.authentica.sa'),
        'otp_digits' => (int) env('AUTHENTICA_OTP_DIGITS', 4),
        'debug_otp' => env('APP_ENV', 'production') === 'local',
        'debug_otp_code' => env('AUTHENTICA_DEBUG_OTP_CODE', '1234'),
    ],

    'tsepass' => [
        'api_key' => env('TSEPASS_API_KEY'),
        'api_url' => env('TSEPASS_API_URL'),
        'legal_entity' => env('TSEPASS_LEGAL_ENTITY', 'adv'),
    ],

];
