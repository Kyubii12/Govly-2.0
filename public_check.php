<?php
/**
 * Temporary Hostinger diagnostic. Delete after the site works.
 * Open: https://stacruzbacnotan.com/public_check.php
 */
header('Content-Type: text/plain; charset=utf-8');
echo "PHP version: " . PHP_VERSION . "\n";
echo "config.php exists: " . (is_file(__DIR__ . '/app/config.php') ? 'yes' : 'no') . "\n";

if (!is_file(__DIR__ . '/app/config.php')) {
    exit;
}

$c = require __DIR__ . '/app/config.php';
echo "db_name: " . ($c['db_name'] ?? '') . "\n";
echo "db_user: " . ($c['db_user'] ?? '') . "\n";
$pass = (string) ($c['db_pass'] ?? '');
echo "db_pass set: " . ($pass !== '' && !str_contains($pass, 'YOUR_DB_PASSWORD') ? 'yes' : 'NO — still placeholder') . "\n";
echo "mail user: " . ($c['mail']['username'] ?? '') . "\n";

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $c['db_host'], $c['db_name']);
    $pdo = new PDO($dsn, $c['db_user'], $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "DB connect: OK\n";
    $n = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . (count($n) ? implode(', ', $n) : '(none — import sql/schema.sql)') . "\n";
} catch (Throwable $e) {
    echo "DB connect: FAIL — " . $e->getMessage() . "\n";
}
