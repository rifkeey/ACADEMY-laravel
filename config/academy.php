<?php

/*
 * AMANIQ Academy application settings. Values come from the environment so that secrets are
 * never committed. Read them with config('academy.*'), never env() outside config files.
 */
return [

    // Canonical public URL used in certificate QR codes and verification links.
    'site_url' => rtrim((string) env('ACADEMY_SITE_URL', env('APP_URL', 'http://localhost')), '/'),

    'issuer' => 'AMANIQ Academy, a division of AMANIQ Projects Limited',

    // Payments: "paystack", "sandbox" (development only, refused in production) or "none".
    'payments' => [
        'provider' => env('PAYMENT_PROVIDER', 'sandbox'),
        'paystack' => [
            'secret_key' => env('PAYSTACK_SECRET_KEY'),
            'api_base' => env('PAYSTACK_API_BASE', 'https://api.paystack.co'),
        ],
    ],

    // Fixed "now" for lab datasets, so KQL ago() results never change with the calendar.
    'lab_reference_now' => '2026-03-12T00:00:00Z',

    // Extra seconds accepted after an exam timer expires, to absorb network latency.
    'exam_submit_grace_seconds' => 30,

    // Staff must use two-factor authentication before reaching the admin area (production only).
    'require_staff_two_factor' => (bool) env('REQUIRE_STAFF_2FA', env('APP_ENV') === 'production'),
];
