<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DatoCMS API Token
    |--------------------------------------------------------------------------
    |
    | Your DatoCMS API token. You can find this in your DatoCMS project settings.
    |
    */
    'api_token' => env('DATOCMS_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | DatoCMS Environment
    |--------------------------------------------------------------------------
    |
    | The environment to use for the DatoCMS API.
    | Default is null which will use the primary environment.
    |
    */
    'environment' => env('DATOCMS_ENVIRONMENT'),

    /*
    |--------------------------------------------------------------------------
    | Preview Mode
    |--------------------------------------------------------------------------
    |
    | Whether to use preview mode for the DatoCMS API.
    | Enable this if you want to fetch draft content.
    |
    */
    'preview' => env('DATOCMS_PREVIEW', false),

    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    |
    | How long to cache API responses for in seconds.
    | Set to null to disable caching.
    |
    */
    'cache_duration' => env('DATOCMS_CACHE_DURATION', 3600),
];
