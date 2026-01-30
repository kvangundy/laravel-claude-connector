<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel Cloud API Token
    |--------------------------------------------------------------------------
    |
    | Your Laravel Cloud API token. Generate this from your organization
    | settings in the Laravel Cloud dashboard.
    |
    */

    'api_token' => env('LARAVEL_CLOUD_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Laravel Cloud API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the Laravel Cloud API.
    |
    */

    'base_url' => env('LARAVEL_CLOUD_API_URL', 'https://cloud.laravel.com/api'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for API requests.
    |
    */

    'timeout' => env('LARAVEL_CLOUD_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic request retries on failure.
    |
    */

    'retry' => [
        'times' => env('LARAVEL_CLOUD_RETRY_TIMES', 3),
        'sleep' => env('LARAVEL_CLOUD_RETRY_SLEEP', 100),
    ],

];
