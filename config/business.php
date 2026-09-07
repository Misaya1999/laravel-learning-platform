<?php

return [
    'name' => env('BUSINESS_NAME', 'KhoaHocPlus'),
    'owner' => env('BUSINESS_OWNER'),
    'address' => env('BUSINESS_ADDRESS'),
    'phone' => env('BUSINESS_PHONE'),
    'email' => env('BUSINESS_EMAIL', env('SUPPORT_EMAIL', 'support@khoahocplus.vn')),
    'tax_code' => env('BUSINESS_TAX_CODE'),
    'policy_version' => env('POLICY_VERSION', '2026-08-19'),
];
