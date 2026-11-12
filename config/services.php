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

    'photon' => [
        'url' => rtrim((string) env('PHOTON_API_URL', 'https://photon.komoot.io/api/'), '/').'/',
    ],

    'twilio' => [
        /*
        | Trial / sandbox: set TWILIO_ENABLED=true with your Account SID, Auth Token,
        | and Twilio phone number. Trial accounts can only SMS verified numbers.
        | Optional TWILIO_SANDBOX_MESSAGE_PREFIX if your sandbox requires a keyword.
        */
        'enabled' => (bool) env('TWILIO_ENABLED', false),
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM'),
        'messaging_service_sid' => env('TWILIO_MESSAGING_SERVICE_SID'),
        'sandbox_message_prefix' => env('TWILIO_SANDBOX_MESSAGE_PREFIX', ''),
        'lead_radius_miles' => (float) env('TWILIO_LEAD_RADIUS_MILES', 100),
    ],

    'square' => [
        /*
        | Square payments for lead purchases. Card data is tokenized in the browser by
        | the Web Payments SDK; only the server-side access token talks to Square.
        | SQUARE_WEBHOOK_URL must equal the notification URL set in the Square
        | webhook subscription (it is part of the signed message).
        */
        'environment' => env('SQUARE_ENVIRONMENT', 'sandbox'),
        'application_id' => env('SQUARE_APPLICATION_ID'),
        'access_token' => env('SQUARE_ACCESS_TOKEN'),
        'location_id' => env('SQUARE_LOCATION_ID'),
        'webhook_signature_key' => env('SQUARE_WEBHOOK_SIGNATURE_KEY'),
        'webhook_url' => env('SQUARE_WEBHOOK_URL'),
        'currency' => env('SQUARE_CURRENCY', 'USD'),
        'api_version' => env('SQUARE_API_VERSION') ?: '2026-09-16',
        'base_url' => env('SQUARE_ENVIRONMENT', 'sandbox') === 'production'
            ? 'https://connect.squareup.com'
            : 'https://connect.squareupsandbox.com',
        'sdk_url' => env('SQUARE_ENVIRONMENT', 'sandbox') === 'production'
            ? 'https://web.squarecdn.com/v1/square.js'
            : 'https://sandbox.web.squarecdn.com/v1/square.js',
    ],

];
