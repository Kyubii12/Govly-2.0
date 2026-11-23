<?php
/**
 * Copy this file to config.php and fill in Hostinger credentials.
 */
return [
    'app_name' => 'GovLy 2.0 — Barangay Sta. Cruz',
    'app_url' => 'https://YOUR-DOMAIN.com', // leave blank to auto-detect
    'timezone' => 'Asia/Manila',
    'barangay' => 'Barangay Sta. Cruz',
    'municipality' => 'Bacnotan',
    'province' => 'La Union',

    'db_host' => 'localhost',
    'db_name' => 'uXXXXXX_stacruz',
    'db_user' => 'uXXXXXX_stacruz',
    'db_pass' => 'YOUR_DB_PASSWORD',
    'db_charset' => 'utf8mb4',

    'mail' => [
        'enabled' => true,
        'host' => 'smtp.hostinger.com',
        'port' => 465,
        'secure' => 'ssl',
        'username' => 'noreply@YOUR-DOMAIN.com',
        'password' => 'YOUR_EMAIL_PASSWORD',
        'from' => 'noreply@YOUR-DOMAIN.com',
        'from_name' => 'GovLy 2.0 · Barangay Sta. Cruz',
    ],

    'otp' => [
        'length' => 6,
        'ttl_min' => 10,
        'max_attempts' => 5,
    ],
];
