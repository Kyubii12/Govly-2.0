<?php
/**
 * Temporary Hostinger diagnostic. Delete after the site works.
 * Upload to public_html then open: https://stacruzbacnotan.com/public_check.php
 */
header('Content-Type: text/plain; charset=utf-8');
echo "PHP version: " . PHP_VERSION . "\n";
echo "config.php exists: " . (is_file(__DIR__ . '/app/config.php') ? 'yes' : 'no') . "\n";

if (!is_file(__DIR__ . '/app/config.php')) {
    exit;
}

$c = require __DIR__ . '/app/config.php';
echo "db_name: " . (isset($c['db_name']) ? $c['db_name'] : '') . "\n";
echo "db_user: " . (isset($c['db_user']) ? $c['db_user'] : '') . "\n";
$pass = isset($c['db_pass']) ? (string) $c['db_pass'] : '';
$passOk = ($pass !== '' && strpos($pass, 'YOUR_DB_PASSWORD') === false);
echo "db_pass set: " . ($passOk ? 'yes' : 'NO — still placeholder') . "\n";
echo "mail user: " . (isset($c['mail']['username']) ? $c['mail']['username'] : '') . "\n";

if (!$passOk) {
    echo "Fix: edit app/config.php and set db_pass to your MySQL password from hPanel.\n";
    exit;
}

try {
    $dsn = 'mysql:host=' . $c['db_host'] . ';dbname=' . $c['db_name'] . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $c['db_user'], $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    echo "DB connect: OK\n";
    $n = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . (count($n) ? implode(', ', $n) : '(none — import sql/schema.sql)') . "\n";
} catch (Exception $e) {
    echo "DB connect: FAIL — " . $e->getMessage() . "\n";
}
