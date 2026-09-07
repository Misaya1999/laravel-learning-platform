<?php

return [
    'payos' => [
        'client_id' => env('PAYOS_CLIENT_ID'),
        'api_key' => env('PAYOS_API_KEY'),
        'checksum_key' => env('PAYOS_CHECKSUM_KEY'),
        'api_url' => env('PAYOS_API_URL', 'https://api-merchant.payos.vn'),
    ],
    'bank' => [
        'name' => env('PAYMENT_BANK_NAME'),
        'account_number' => env('PAYMENT_BANK_ACCOUNT_NUMBER'),
        'account_name' => env('PAYMENT_BANK_ACCOUNT_NAME'),
        'branch' => env('PAYMENT_BANK_BRANCH'),
    ],
    'pending_expiration_hours' => (int) env('PAYMENT_PENDING_EXPIRATION_HOURS', 48),
];
