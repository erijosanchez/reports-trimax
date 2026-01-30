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

    'google' => [
        'sheets' => [
            'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID', '12SKxU3bvZ4psujz0DVfx-el1jKgbBX7S8pw_ngX7Ezg'),
            'sheet_name' => env('GOOGLE_SHEETS_SHEET_NAME', 'ORD'),
        ],
        'service_account' => [
            'json_location' => env('GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION', 'app/google/service-account.json'),
        ],
        'scopes' => [
            'https://www.googleapis.com/auth/spreadsheets',
            'https://www.googleapis.com/auth/drive'
        ],
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'api_url' => 'https://api.groq.com/openai/v1/chat/completions',
        'model' => env('GROQ_MODEL', 'llama-3.1-8b-instant'),
    ],

];
