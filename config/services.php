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
        // Incoming webhook used to simulate operational alerts in the demo.
        'webhook_url' => env('SLACK_WEBHOOK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI
    |--------------------------------------------------------------------------
    |
    | Credentials for the AI analysis layer. The IoT telemetry batches are
    | summarised by a chat completion model which returns a structured JSON
    | verdict (status / summary / recommended_action).
    |
    */

    'openai' => [
        'enabled' => (bool) env('AI_ANALYSIS_ENABLED', true),
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'timeout' => (int) env('OPENAI_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sensor Alerts
    |--------------------------------------------------------------------------
    |
    | Destinations used by the simulated operational notifications. The email
    | is delivered through the configured mailer (the "log" mailer writes a
    | full MIME message to storage/logs, perfect for a demo). A Slack incoming
    | webhook can be supplied to post a realistic payload as well.
    |
    */

    'sensor_alerts' => [
        'mail_to' => env('SENSOR_ALERT_MAIL_TO', 'operator@example.com'),
        'slack_webhook' => env('SLACK_WEBHOOK_URL'),
    ],

];
