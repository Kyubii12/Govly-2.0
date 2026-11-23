<?php
declare(strict_types=1);

// Show errors while you finish Hostinger setup (turn off later)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$configPath = __DIR__ . '/config.php';
if (!is_file($configPath)) {
    http_response_code(500);
    exit('Missing app/config.php. Copy config.example.php to config.php and fill Hostinger MySQL + SMTP credentials.');
}

$GLOBALS['CONFIG'] = require $configPath;
date_default_timezone_set($GLOBALS['CONFIG']['timezone'] ?? 'Asia/Manila');

if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    http_response_code(500);
    exit('GovLy 2.0 needs PHP 8.0+. In hPanel → Advanced → PHP Configuration, set PHP to 8.2 or 8.3.');
}

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/mail.php';
