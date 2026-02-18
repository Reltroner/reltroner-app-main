<?php
// config/services.php
// Context: app.reltroner.com (Auth Gateway)

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services (UNCHANGED)
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'             => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Keycloak — PHASE 2 (FROZEN)
    |--------------------------------------------------------------------------
    | Auth authority (DO NOT MIX WITH GATEWAY)
    |--------------------------------------------------------------------------
    */

    'keycloak' => [
        'base_url'     => env('KEYCLOAK_BASE_URL'),
        'realm'        => env('KEYCLOAK_REALM'),
        'client_id'    => env('KEYCLOAK_CLIENT_ID'),
        'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
        'redirect_uri' => env('KEYCLOAK_REDIRECT_URI'),
    ],


    /*
    |--------------------------------------------------------------------------
    | Reltroner Auth Gateway — PHASE 3
    |--------------------------------------------------------------------------
    | Gateway = RMAT issuer (NOT Keycloak)
    |--------------------------------------------------------------------------
    */

    'gateway' => [

        /*
        | Issuer claim for RMAT
        | MUST match Finance expectation
        */
        'issuer' => env(
            'RELTRONER_GATEWAY_ISSUER',
            'https://app.reltroner.com'
        ),

        /*
        | Shared signing key (HS256)
        | MUST be identical with Finance
        */
        'signing_key' => env('RELTRONER_MODULE_SIGNING_KEY'),
    ],


    /*
    |--------------------------------------------------------------------------
    | ERP Modules — PHASE 3
    |--------------------------------------------------------------------------
    | Used ONLY for redirect target (NOT auth)
    |--------------------------------------------------------------------------
    */

    'modules' => [

        // Finance ERP (Phase 3 active)
        'finance' => env(
            'FINANCE_MODULE_URL',
            'https://finance.reltroner.com'
        ),

        // future (Phase 4+)
        // 'hrm'       => env('HRM_MODULE_URL'),
        // 'inventory'=> env('INVENTORY_MODULE_URL'),
        // 'crm'       => env('CRM_MODULE_URL'),
    ],

];
