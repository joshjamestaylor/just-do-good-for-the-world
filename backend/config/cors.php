<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The decoupled/detached frontend is served from a different origin (the
    | Nuxt dev server, or Netlify), so the JSON API must allow cross-origin
    | requests. Lock CORS_ALLOWED_ORIGINS down to your frontend origin(s) in
    | production; the default "*" is convenient for local development.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(
        array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '*')))
    ),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['ETag'],

    'max_age' => 0,

    'supports_credentials' => false,

];
