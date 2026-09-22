<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Feature Flags & Master Switches
    |--------------------------------------------------------------------------
    |
    | Master flag and sub-feature flags. Default to false for safe pilot rollout.
    |
    */
    'enabled' => env('AI_ENABLED', true),
    'reports_enabled' => env('AI_REPORTS_ENABLED', true),
    'chat_enabled' => env('AI_CHAT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Gemini API Configuration (Reporting & Analytics)
    |--------------------------------------------------------------------------
    */
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        'endpoint' => env('GEMINI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models'),
        'timeout' => (int) env('AI_GEMINI_TIMEOUT', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Anthropic Claude Configuration (Chatbot, BD & Lead Gen)
    |--------------------------------------------------------------------------
    */
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY', ''),
        'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
        'endpoint' => env('ANTHROPIC_ENDPOINT', 'https://api.anthropic.com/v1/messages'),
        'timeout' => (int) env('AI_CLAUDE_TIMEOUT', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache & Performance Settings
    |--------------------------------------------------------------------------
    */
    'cache_ttl_minutes' => (int) env('AI_REPORT_CACHE_TTL', 30),
];
