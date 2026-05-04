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

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configuration
    |--------------------------------------------------------------------------
    */
    'payment_gateway' => [
        'api_key' => env('PAYMENT_GATEWAY_API_KEY', ''),
        'api_secret' => env('PAYMENT_GATEWAY_API_SECRET', ''),
        'api_url' => env('PAYMENT_GATEWAY_API_URL', 'https://api.payment-gateway.com/v1'),
        'environment' => env('PAYMENT_GATEWAY_ENVIRONMENT', 'sandbox'), // sandbox ou production

        // Configurações específicas por bandeira
        'credit_card' => [
            'max_installments' => 12,
            'min_installment_value' => 10.00,
            'default_fee_percentage' => 3.0,
        ],

        'boleto' => [
            'default_due_days' => 3,
            'max_amount' => 50000.00,
        ],

        'pix' => [
            'expiration_minutes' => 30,
            'max_amount' => 10000.00,
        ],
    ],

];
