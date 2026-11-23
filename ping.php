<?php
// Ultra-simple Hostinger test. Upload to public_html then open:
// https://stacruzbacnotan.com/ping.php
header('Content-Type: text/plain; charset=utf-8');
echo "OK\n";
echo "PHP=" . PHP_VERSION . "\n";
echo "time=" . date('c') . "\n";
echo "config=" . (file_exists(__DIR__ . '/app/config.php') ? 'yes' : 'no') . "\n";
