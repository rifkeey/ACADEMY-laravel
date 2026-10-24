<?php

/*
 * AMANIQ Academy application settings. Values come from the environment so that secrets are
 * never committed. Read them with config('academy.*'), never env() outside config files.
 */
return [

    // Canonical public URL used in certificate QR codes and verification links.
    'site_url' => rtrim((string) env('ACADEMY_SITE_URL', env('APP_URL', 'http://localhost')), '/'),

    'issuer' => 'AMANIQ Academy, a division of AMANIQ Projects Limited',

    // Interface languages. English is the default and keeps the existing URLs; other languages
    // get a URL prefix for public pages (/fr/...). "currency" is the default display currency.
    'locales' => [
        'en' => ['name' => 'English', 'short' => 'EN', 'html' => 'en-GB', 'currency' => 'NGN'],
        'fr' => ['name' => 'Français', 'short' => 'FR', 'html' => 'fr', 'currency' => 'XOF'],
    ],
    'default_locale' => 'en',

    // Selling currencies. "decimals" is the ISO 4217 minor-unit exponent: amounts are stored as
    // integers in that unit (kobo for NGN; whole francs for XOF, which has no minor unit).
    // Prices are set per currency by an administrator; nothing is converted at checkout.
    'currencies' => [
        'NGN' => ['decimals' => 2, 'symbol' => '₦', 'short' => 'NGN'],
        'XOF' => ['decimals' => 0, 'symbol' => 'FCFA', 'short' => 'FCFA'],
    ],

    // Payments: "paystack", "sandbox" (development only, refused in production) or "none".
    'payments' => [
        'provider' => env('PAYMENT_PROVIDER', 'sandbox'),
        'paystack' => [
            'secret_key' => env('PAYSTACK_SECRET_KEY'),
            'api_base' => env('PAYSTACK_API_BASE', 'https://api.paystack.co'),
            // Comma-separated currencies enabled on the Paystack account. XOF is not offered here.
            'currencies' => env('PAYSTACK_CURRENCIES', 'NGN'),
        ],
    ],

    // Fixed "now" for lab datasets, so KQL ago() results never change with the calendar.
    'lab_reference_now' => '2026-03-12T00:00:00Z',

    // Extra seconds accepted after an exam timer expires, to absorb network latency.
    'exam_submit_grace_seconds' => 30,

    // Staff must use two-factor authentication before reaching the admin area (production only).
    'require_staff_two_factor' => (bool) env('REQUIRE_STAFF_2FA', env('APP_ENV') === 'production'),
];
