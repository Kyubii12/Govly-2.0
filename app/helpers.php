<?php
declare(strict_types=1);

function h($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_url(string $path = ''): string
{
    $cfg = $GLOBALS['CONFIG']['app_url'] ?? '';
    if ($cfg) {
        return rtrim($cfg, '/') . '/' . ltrim($path, '/');
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    return $scheme . '://' . $host . ($dir === '' || $dir === '/' ? '' : $dir) . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : base_url($path)));
    exit;
}

function flash(string $key, ?string $msg = null)
{
    if ($msg === null) {
        $v = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $v;
    }
    $_SESSION['flash'][$key] = $msg;
}

function puroks(): array
{
    return ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5', 'Purok 6', 'Purok 7'];
}

function positions(): array
{
    return [
        'Punong Barangay', 'Kagawad', 'Barangay Secretary', 'Barangay Treasurer',
        'Barangay Tanod', 'Barangay Health Worker', 'Barangay Nutrition Scholar', 'Barangay Midwife',
    ];
}

function cert_types(): array
{
    $fallback = [
        'Barangay Clearance', 'Certificate of Residency', 'Certificate of Indigency',
        'Business Permit', 'Solo Parent ID Application', 'Solo Parent Assistance',
    ];
    try {
        $rows = db_all('SELECT type FROM cert_templates ORDER BY type');
        $types = array_column($rows, 'type');
        return $types ?: $fallback;
    } catch (Throwable $e) {
        return $fallback;
    }
}

function default_site(): array
{
    return [
        'emergencyCall' => '911',
        'emergencyLead' => 'For an immediate emergency in Barangay Sta. Cruz, Bacnotan, La Union, call 911 or the appropriate local response number directly.',
        'officeAddress' => "Barangay Sta. Cruz, Bacnotan\nLa Union, Philippines",
        'contactPhones' => "0951-960-2398\n0919-546-0617",
        'contactEmail' => 'brgy.stacruz@bacnotan.gov.ph',
        'officeHours' => "Monday – Friday: 8:00 AM – 5:00 PM\nSaturday: 8:00 AM – 12:00 PM",
        'footerLocation' => 'Bacnotan, La Union',
        'services' => [
            ['type' => 'Barangay Clearance', 'price' => '₱50.00', 'time' => '1-2 days', 'desc' => 'Official clearance certificate for employment, business, and legal purposes.'],
            ['type' => 'Certificate of Residency', 'price' => '₱50.00', 'time' => '1-2 days', 'desc' => 'Proof of residence for government transactions and applications.'],
            ['type' => 'Certificate of Indigency', 'price' => 'FREE', 'time' => '1-2 days', 'desc' => 'For medical, educational, and social welfare assistance programs.'],
            ['type' => 'Business Permit', 'price' => '₱500.00+', 'time' => '3-5 days', 'desc' => 'Barangay business clearance for new and renewal applications.'],
            ['type' => 'Solo Parent ID Application', 'price' => 'FREE', 'time' => 'Same day', 'desc' => 'Official identification for solo parents to access government benefits and programs.'],
            ['type' => 'Solo Parent Assistance', 'price' => 'FREE', 'time' => 'By schedule', 'desc' => 'Financial and livelihood assistance programs for solo parents and their families.'],
        ],
        'dispatch' => [
            ['name' => '911 — National / La Union Rescue', 'phones' => ['911'], 'purpose' => 'Call for an immediate emergency.', 'note' => 'Use 911 first when life, fire, crime, or rescue is involved.'],
            ['name' => 'Bacnotan Rescue', 'phones' => ['0919-546-1800'], 'purpose' => 'Municipal rescue and response dispatch.', 'note' => ''],
            ['name' => 'MDRRMO Bacnotan', 'phones' => ['0949-474-2161', '0917-773-9994'], 'purpose' => 'Disaster, rescue, and emergency coordination.', 'note' => ''],
            ['name' => 'PNP Bacnotan', 'phones' => ['0998-598-5156', '0915-513-7245'], 'purpose' => 'Police and public-safety contact.', 'note' => ''],
            ['name' => 'BFP Bacnotan', 'phones' => ['0917-184-2711', '(072) 607-5472'], 'purpose' => 'Fire and related emergencies.', 'note' => ''],
        ],
        'related' => [
            ['name' => 'Barangay Sta. Cruz Hall', 'kind' => 'Barangay office', 'phones' => ['0951-960-2398', '0919-546-0617']],
            ['name' => 'Bacnotan District Hospital', 'kind' => 'Health', 'phones' => ['0907-724-8269']],
            ['name' => 'Municipal Health Office', 'kind' => 'Health', 'phones' => ['0909-847-1268', '0917-718-9585']],
            ['name' => 'MSWDO Bacnotan', 'kind' => 'Social welfare', 'phones' => ['0928-520-3893']],
            ['name' => 'LUELCO Bacnotan', 'kind' => 'Utility', 'phones' => ['0917-120-4671']],
            ['name' => 'Office of the Municipal Mayor', 'kind' => 'LGU office', 'phones' => ['0929-581-0383']],
        ],
    ];
}

function site(): array
{
    $base = default_site();
    try {
        $row = db_one("SELECT content FROM settings WHERE id = 'portal'");
    } catch (Throwable $e) {
        return $base;
    }
    $saved = $row ? json_decode($row['content'], true) : [];
    if (!is_array($saved)) {
        return $base;
    }
    foreach (['emergencyCall', 'emergencyLead', 'officeAddress', 'contactPhones', 'contactEmail', 'officeHours', 'footerLocation'] as $k) {
        if (!empty($saved[$k])) {
            $base[$k] = $saved[$k];
        }
    }
    if (!empty($saved['services']) && is_array($saved['services'])) {
        $base['services'] = $saved['services'];
    }
    if (!empty($saved['dispatch']) && is_array($saved['dispatch'])) {
        $base['dispatch'] = $saved['dispatch'];
    }
    if (!empty($saved['related']) && is_array($saved['related'])) {
        $base['related'] = $saved['related'];
    }
    return $base;
}

function save_site(array $content): void
{
    db_exec(
        "INSERT INTO settings (id, content) VALUES ('portal', ?) ON DUPLICATE KEY UPDATE content = VALUES(content)",
        [json_encode($content, JSON_UNESCAPED_UNICODE)]
    );
}

function age_from_birth(?string $date): ?int
{
    if (!$date) {
        return null;
    }
    try {
        return (int) (new DateTime($date))->diff(new DateTime('today'))->y;
    } catch (Throwable $e) {
        return null;
    }
}

function age_group(?int $age): string
{
    if ($age === null) {
        return 'Unknown';
    }
    if ($age < 18) {
        return '0-17';
    }
    if ($age < 60) {
        return '18-59';
    }
    return '60+';
}

function issued_today(): string
{
    $d = (int) date('j');
    $suf = ($d > 3 && $d < 21) ? 'th' : match ($d % 10) {
        1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th',
    };
    return $d . $suf . ' day of ' . strtoupper(date('F')) . ', ' . date('Y');
}

function make_control_no(): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $tail = '';
    for ($i = 0; $i < 5; $i++) {
        $tail .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return 'SC-' . date('Ymd') . '-' . $tail;
}

function fill_placeholders(string $text, string $name, string $address): string
{
    return str_replace(
        ['RESIDENT NAME', 'ADDRESS'],
        [$name ?: 'RESIDENT NAME', $address ?: 'Barangay Sta. Cruz, Bacnotan, La Union'],
        $text
    );
}

function service_price(array $site, string $type): string
{
    foreach ($site['services'] ?? [] as $s) {
        if (strcasecmp((string) ($s['type'] ?? ''), $type) === 0) {
            return (string) ($s['price'] ?? '—');
        }
    }
    return '—';
}

function normalize_phone(string $input): string
{
    $d = preg_replace('/\D+/', '', $input) ?? '';
    if (str_starts_with($d, '63') && strlen($d) === 12) {
        return '0' . substr($d, 2);
    }
    if (str_starts_with($d, '9') && strlen($d) === 10) {
        return '0' . $d;
    }
    return $d;
}

function tel_href(string $label): string
{
    $d = preg_replace('/\D+/', '', $label) ?? '';
    if ($d === '911') {
        return 'tel:911';
    }
    if (str_starts_with($d, '0') && strlen($d) === 11) {
        return 'tel:+63' . substr($d, 1);
    }
    return 'tel:' . $d;
}

function is_admin_user(?array $user): bool
{
    return $user && in_array($user['role'] ?? '', ['admin', 'super_admin'], true);
}

function is_active_resident(array $user): bool
{
    if (($user['role'] ?? '') !== 'resident') {
        return false;
    }
    if (($user['status'] ?? '') !== 'active') {
        return false;
    }
    $row = db_one('SELECT status, authorized, user_id FROM residents WHERE user_id = ? OR phone = ? LIMIT 1', [
        $user['id'], $user['phone'] ?? '',
    ]);
    if (!$row) {
        // Approved website account without census row is still allowed
        return true;
    }
    return $row['status'] === 'Active' || (int) $row['authorized'] === 1 || !empty($row['user_id']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): array
{
    $u = current_user();
    if (!$u) {
        flash('error', 'Please log in to continue.');
        redirect('login');
    }
    return $u;
}

function require_admin(): array
{
    $u = require_login();
    if (!is_admin_user($u)) {
        flash('error', 'Admin access only.');
        redirect('dashboard');
    }
    return $u;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    unset($user['password_hash']);
    $_SESSION['user'] = $user;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
    }
    session_destroy();
}

function post(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function route(): string
{
    $r = trim((string) ($_GET['r'] ?? ''), '/');
    return $r === '' ? 'home' : $r;
}

function log_activity(string $action, string $detail = ''): void
{
    $u = current_user();
    try {
        db_insert(
            'INSERT INTO activity_logs (action, detail, actor, actor_role) VALUES (?,?,?,?)',
            [$action, $detail, $u ? trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) : 'system', $u['role'] ?? 'system']
        );
    } catch (Throwable $e) {
        // table may not exist yet during first install
    }
}

function create_email_otp(string $email, string $purpose = 'register'): string
{
    $cfg = $GLOBALS['CONFIG']['otp'] ?? [];
    $len = (int) ($cfg['length'] ?? 6);
    $ttl = (int) ($cfg['ttl_min'] ?? 10);
    $otp = '';
    for ($i = 0; $i < $len; $i++) {
        $otp .= (string) random_int(0, 9);
    }
    db_exec('DELETE FROM email_otps WHERE email = ? AND purpose = ?', [$email, $purpose]);
    db_insert(
        'INSERT INTO email_otps (email, otp_hash, purpose, expires_at) VALUES (?,?,?, DATE_ADD(NOW(), INTERVAL ? MINUTE))',
        [$email, password_hash($otp, PASSWORD_DEFAULT), $purpose, $ttl]
    );
    return $otp;
}

function verify_email_otp(string $email, string $otp, string $purpose = 'register'): bool
{
    $cfg = $GLOBALS['CONFIG']['otp'] ?? [];
    $max = (int) ($cfg['max_attempts'] ?? 5);
    $row = db_one(
        'SELECT * FROM email_otps WHERE email = ? AND purpose = ? AND consumed_at IS NULL ORDER BY id DESC LIMIT 1',
        [$email, $purpose]
    );
    if (!$row) {
        return false;
    }
    if (strtotime($row['expires_at']) < time()) {
        return false;
    }
    if ((int) $row['attempts'] >= $max) {
        return false;
    }
    if (!password_verify($otp, $row['otp_hash'])) {
        db_exec('UPDATE email_otps SET attempts = attempts + 1 WHERE id = ?', [(int) $row['id']]);
        return false;
    }
    db_exec('UPDATE email_otps SET consumed_at = NOW() WHERE id = ?', [(int) $row['id']]);
    return true;
}

function full_name(array $row): string
{
    return trim(($row['first_name'] ?? '') . ' ' . ($row['middle_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
}
