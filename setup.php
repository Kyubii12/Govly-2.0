<?php
/**
 * GovLy 2.0 one-time setup for Hostinger.
 * 1) Upload this file to public_html
 * 2) Open https://stacruzbacnotan.com/setup.php
 * 3) Fill MySQL + email, click Save
 * 4) DELETE this file after it works
 */
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

$configPath = __DIR__ . '/app/config.php';
$examplePath = __DIR__ . '/app/config.example.php';
$schemaPath = __DIR__ . '/sql/schema.sql';
$msg = '';
$ok = false;

function h($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim((string) ($_POST['db_host'] ?? 'localhost'));
    $dbName = trim((string) ($_POST['db_name'] ?? ''));
    $dbUser = trim((string) ($_POST['db_user'] ?? ''));
    $dbPass = (string) ($_POST['db_pass'] ?? '');
    $mailUser = trim((string) ($_POST['mail_user'] ?? ''));
    $mailPass = (string) ($_POST['mail_pass'] ?? '');
    $appUrl = rtrim(trim((string) ($_POST['app_url'] ?? 'https://stacruzbacnotan.com')), '/');
    $import = !empty($_POST['import_schema']);

    if ($dbName === '' || $dbUser === '' || $dbPass === '') {
        $msg = 'Database name, user, and password are required.';
    } else {
        try {
            $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            if ($import && is_file($schemaPath)) {
                $sql = file_get_contents($schemaPath);
                // Split roughly on semicolons for Hostinger phpMyAdmin-less import
                $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
                foreach (preg_split('/;\s*\n/', $sql) as $chunk) {
                    $chunk = trim($chunk);
                    if ($chunk === '' || str_starts_with($chunk, '--')) {
                        continue;
                    }
                    try {
                        $pdo->exec($chunk);
                    } catch (Throwable $e) {
                        // ignore duplicate table/seed errors
                    }
                }
                $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            }

            $php = "<?php\n"
                . "return [\n"
                . "    'app_name' => 'GovLy 2.0 — Barangay Sta. Cruz',\n"
                . "    'app_url' => " . var_export($appUrl, true) . ",\n"
                . "    'timezone' => 'Asia/Manila',\n"
                . "    'barangay' => 'Barangay Sta. Cruz',\n"
                . "    'municipality' => 'Bacnotan',\n"
                . "    'province' => 'La Union',\n"
                . "    'db_host' => " . var_export($dbHost, true) . ",\n"
                . "    'db_name' => " . var_export($dbName, true) . ",\n"
                . "    'db_user' => " . var_export($dbUser, true) . ",\n"
                . "    'db_pass' => " . var_export($dbPass, true) . ",\n"
                . "    'db_charset' => 'utf8mb4',\n"
                . "    'mail' => [\n"
                . "        'enabled' => true,\n"
                . "        'host' => 'smtp.hostinger.com',\n"
                . "        'port' => 465,\n"
                . "        'secure' => 'ssl',\n"
                . "        'username' => " . var_export($mailUser, true) . ",\n"
                . "        'password' => " . var_export($mailPass, true) . ",\n"
                . "        'from' => " . var_export($mailUser !== '' ? $mailUser : 'noreply@stacruzbacnotan.com', true) . ",\n"
                . "        'from_name' => 'GovLy 2.0 · Barangay Sta. Cruz',\n"
                . "    ],\n"
                . "    'otp' => [\n"
                . "        'length' => 6,\n"
                . "        'ttl_min' => 10,\n"
                . "        'max_attempts' => 5,\n"
                . "    ],\n"
                . "];\n";

            if (!is_dir(__DIR__ . '/app')) {
                throw new RuntimeException('Missing /app folder. Upload the full GovLy 2.0 project first.');
            }
            if (file_put_contents($configPath, $php) === false) {
                throw new RuntimeException('Could not write app/config.php. Check File Manager permissions.');
            }

            $ok = true;
            $msg = 'Saved! Database connected'
                . ($import ? ' and schema import attempted' : '')
                . '. Open the homepage, then DELETE setup.php for security.';
        } catch (Throwable $e) {
            $msg = 'Failed: ' . $e->getMessage();
        }
    }
}

$existing = is_file($configPath) ? (require $configPath) : [];
$dbName = $existing['db_name'] ?? 'u108914767_6C3IW';
$dbUser = $existing['db_user'] ?? 'u108914767_IVqxm';
$mailUser = $existing['mail']['username'] ?? 'noreply@stacruzbacnotan.com';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>GovLy 2.0 Setup</title>
  <style>
    body { font-family: Segoe UI, sans-serif; background:#0b1220; color:#e5e7eb; margin:0; padding:24px; }
    .card { max-width:560px; margin:0 auto; background:#111827; border:1px solid #1f2937; border-radius:16px; padding:24px; }
    h1 { margin:0 0 8px; color:#93c5fd; }
    label { display:block; margin:12px 0 4px; font-weight:600; }
    input { width:100%; padding:10px 12px; border-radius:10px; border:1px solid #334155; background:#0b1220; color:#fff; box-sizing:border-box; }
    button { margin-top:16px; width:100%; padding:12px; border:0; border-radius:10px; background:#e68a00; color:#fff; font-weight:800; cursor:pointer; }
    .msg { padding:12px; border-radius:10px; margin:12px 0; }
    .ok { background:#052e16; color:#86efac; }
    .err { background:#450a0a; color:#fca5a5; }
    .hint { color:#94a3b8; font-size:.9rem; }
    a { color:#93c5fd; }
  </style>
</head>
<body>
  <div class="card">
    <h1>GovLy 2.0 Setup</h1>
    <p class="hint">Fill your Hostinger MySQL + email. This writes <code>app/config.php</code> for you.</p>
    <?php if ($msg): ?>
      <div class="msg <?= $ok ? 'ok' : 'err' ?>"><?= h($msg) ?></div>
      <?php if ($ok): ?>
        <p><a href="/">Open homepage</a> · <a href="/login">Login</a></p>
        <p class="hint">Admin: admin@stacruzbacnotan.com / Admin@12345</p>
      <?php endif; ?>
    <?php endif; ?>
    <form method="post">
      <label>App URL</label>
      <input name="app_url" value="<?= h($_POST['app_url'] ?? 'https://stacruzbacnotan.com') ?>" />
      <label>DB host</label>
      <input name="db_host" value="<?= h($_POST['db_host'] ?? 'localhost') ?>" />
      <label>DB name</label>
      <input name="db_name" required value="<?= h($_POST['db_name'] ?? $dbName) ?>" />
      <label>DB user</label>
      <input name="db_user" required value="<?= h($_POST['db_user'] ?? $dbUser) ?>" />
      <label>DB password (from hPanel → MySQL)</label>
      <input name="db_pass" type="password" required value="<?= h($_POST['db_pass'] ?? '') ?>" />
      <label>Email (OTP sender)</label>
      <input name="mail_user" value="<?= h($_POST['mail_user'] ?? $mailUser) ?>" />
      <label>Email password</label>
      <input name="mail_pass" type="password" value="<?= h($_POST['mail_pass'] ?? '') ?>" />
      <label style="display:flex;gap:8px;align-items:center;font-weight:500">
        <input type="checkbox" name="import_schema" value="1" checked style="width:auto" />
        Import sql/schema.sql now
      </label>
      <button type="submit">Save config &amp; connect</button>
    </form>
    <p class="hint" style="margin-top:16px">Get DB password: hPanel → Databases → MySQL → your database user.</p>
  </div>
</body>
</html>
