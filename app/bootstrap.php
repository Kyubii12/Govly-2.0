<?php
declare(strict_types=1);

session_start();

$configPath = __DIR__ . '/config.php';
if (!is_file($configPath)) {
    http_response_code(500);
    exit('Missing app/config.php. Copy config.example.php to config.php and fill Hostinger MySQL + SMTP credentials.');
}

$GLOBALS['CONFIG'] = require $configPath;
date_default_timezone_set($GLOBALS['CONFIG']['timezone'] ?? 'Asia/Manila');

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/mail.php';
