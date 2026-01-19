<?php

return [
    'webhook_url' => env('MESSAGING_WEBHOOK_URL'),

    'char_limit' => (int) env('MESSAGING_CHAR_LIMIT', 160),

    'dispatch_batch_limit' => (int) env('MESSAGE_DISPATCH_BATCH_LIMIT', 500),

    'rate_limit' => [
        'enabled' => filter_var(env('MESSAGE_ENABLED', true), FILTER_VALIDATE_BOOL),
        'key' => env('MESSAGE_KEY', 'bulk-message-sender'),
        'allow' => (int) env('MESSAGE_ALLOW', 2),
        'every_seconds' => (int) env('MESSAGE_EVERY_SECONDS', 5),
        'release_seconds' => (int) env('MESSAGE_RELEASE_SECONDS', 1),
    ],

    'sent_cache' => [
        'enabled' => filter_var(env('MESSAGE_SENT_CACHE_ENABLED', true), FILTER_VALIDATE_BOOL),
        'ttl_seconds' => (int) env('MESSAGE_SENT_CACHE_TTL_SECONDS', 604800), // 7 days
        'key_prefix' => env('MESSAGE_SENT_CACHE_KEY_PREFIX', 'message:sent:'),
    ],
];
