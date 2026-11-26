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
    $types = $fallback;
    try {
        $rows = db_all('SELECT type FROM cert_templates ORDER BY type');
        foreach (array_column($rows, 'type') as $t) {
            $t = trim((string) $t);
            if ($t !== '' && !in_array($t, $types, true)) {
                $types[] = $t;
            }
        }
    } catch (Throwable $e) {
        // keep fallbacks
    }
    try {
        foreach ((site()['services'] ?? []) as $s) {
            $t = trim((string) ($s['type'] ?? ''));
            if ($t !== '' && !in_array($t, $types, true)) {
                $types[] = $t;
            }
        }
    } catch (Throwable $e) {
        // ignore
    }
    return $types;
}

function default_cert_template(string $type = ''): array
{
    $defaults = [
        'Barangay Clearance' => [
            'body' => 'This is to certify that RESIDENT NAME, of legal age, is a bona fide resident of ADDRESS and is known to this office to be a person of good moral character, with no pending complaint or derogatory record on file in this barangay.',
        ],
        'Certificate of Residency' => [
            'body' => 'This is to certify that RESIDENT NAME, of legal age, is a bona fide resident of ADDRESS.',
        ],
        'Certificate of Indigency' => [
            'body' => 'This is to certify that RESIDENT NAME, of legal age, is a bona fide resident of ADDRESS and that their family had low income as considered an indigent family in this barangay.',
        ],
        'Business Permit' => [
            'body' => 'This is to certify that RESIDENT NAME, a bona fide resident of ADDRESS, is granted barangay clearance in connection with a business application as stated below.',
        ],
        'Solo Parent ID Application' => [
            'body' => 'This is to certify that RESIDENT NAME, a bona fide resident of ADDRESS, is a qualified solo parent of this barangay.',
        ],
        'Solo Parent Assistance' => [
            'body' => 'This is to certify that RESIDENT NAME, a bona fide resident of ADDRESS, is a qualified solo parent and may be considered for assistance programs of this barangay.',
        ],
    ];
    $blank = [
        'body' => 'This is to certify that RESIDENT NAME, of legal age, is a bona fide resident of ADDRESS.',
        'purpose' => '',
        'details' => '',
        'closing' => 'This certification is issued upon the request of the party concerned for intents and purposes it may serve.',
    ];
    if (isset($defaults[$type])) {
        return array_merge($blank, $defaults[$type]);
    }
    return $blank;
}

function get_cert_template(string $type): array
{
    $fallback = default_cert_template($type);
    if ($type === '') {
        return $fallback;
    }
    try {
        $row = db_one('SELECT * FROM cert_templates WHERE type = ?', [$type]);
    } catch (Throwable $e) {
        return $fallback;
    }
    if (!$row) {
        return $fallback;
    }
    return [
        'body' => ($row['body'] ?? '') !== '' ? $row['body'] : $fallback['body'],
        'purpose' => $row['purpose'] ?? $fallback['purpose'],
        'details' => $row['details'] ?? $fallback['details'],
        'closing' => ($row['closing'] ?? '') !== '' ? $row['closing'] : $fallback['closing'],
    ];
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
        'barangayName' => 'Barangay Sta. Cruz',
        'municipality' => 'Bacnotan',
        'province' => 'La Union',
        'systemLanguage' => 'English',
        'timeZone' => 'Asia/Manila',
        'dateFormat' => 'F j, Y',
        'enableNotifications' => '1',
        'autoSaveRecords' => '1',
        'publicAnnouncementAlerts' => '1',
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
    foreach ([
        'emergencyCall', 'emergencyLead', 'officeAddress', 'contactPhones', 'contactEmail', 'officeHours', 'footerLocation',
        'barangayName', 'municipality', 'province', 'systemLanguage', 'timeZone', 'dateFormat',
        'enableNotifications', 'autoSaveRecords', 'publicAnnouncementAlerts',
    ] as $k) {
        if (array_key_exists($k, $saved) && $saved[$k] !== '' && $saved[$k] !== null) {
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
        return 'unknown';
    }
    if ($age < 18) {
        return 'minor';
    }
    if ($age >= 60) {
        return 'senior';
    }
    return 'other';
}

function age_group_label(?string $group): string
{
    return match ($group) {
        'minor' => 'Minor',
        'senior' => 'Senior citizen',
        'other' => '18–59',
        'unknown' => 'No age',
        default => 'No age',
    };
}

function is_super_admin(?array $user): bool
{
    if (!$user) {
        return false;
    }
    $email = strtolower((string) ($user['email'] ?? ''));
    return ($user['role'] ?? '') === 'super_admin' || $email === 'admin@brgy.com';
}

function role_label(?array $user): string
{
    if (is_super_admin($user)) {
        return 'Super Admin';
    }
    if (is_admin_user($user)) {
        return 'Admin';
    }
    return 'Resident';
}

function psa_individual_bands(): array
{
    return [
        ['id' => 'u1', 'label' => 'Under 1', 'min' => 0, 'max' => 0],
        ['id' => '1-4', 'label' => '1 to 4', 'min' => 1, 'max' => 4],
        ['id' => '5-9', 'label' => '5 to 9', 'min' => 5, 'max' => 9],
        ['id' => '10-14', 'label' => '10 to 14', 'min' => 10, 'max' => 14],
        ['id' => '15-19', 'label' => '15 to 19', 'min' => 15, 'max' => 19],
        ['id' => '20-24', 'label' => '20 to 24', 'min' => 20, 'max' => 24],
        ['id' => '25-29', 'label' => '25 to 29', 'min' => 25, 'max' => 29],
        ['id' => '30-34', 'label' => '30 to 34', 'min' => 30, 'max' => 34],
        ['id' => '35-39', 'label' => '35 to 39', 'min' => 35, 'max' => 39],
        ['id' => '40-44', 'label' => '40 to 44', 'min' => 40, 'max' => 44],
        ['id' => '45-49', 'label' => '45 to 49', 'min' => 45, 'max' => 49],
        ['id' => '50-54', 'label' => '50 to 54', 'min' => 50, 'max' => 54],
        ['id' => '55-59', 'label' => '55 to 59', 'min' => 55, 'max' => 59],
        ['id' => '60-64', 'label' => '60 to 64', 'min' => 60, 'max' => 64],
        ['id' => '65-69', 'label' => '65 to 69', 'min' => 65, 'max' => 69],
        ['id' => '70-74', 'label' => '70 to 74', 'min' => 70, 'max' => 74],
        ['id' => '75-79', 'label' => '75 to 79', 'min' => 75, 'max' => 79],
        ['id' => '80+', 'label' => '80 and over', 'min' => 80, 'max' => 200],
    ];
}

function psa_broad_bands(): array
{
    return [
        ['id' => '0-14', 'label' => '0 to 14', 'min' => 0, 'max' => 14],
        ['id' => '15-64', 'label' => '15 to 64', 'min' => 15, 'max' => 64],
        ['id' => '65+', 'label' => '65 and over', 'min' => 65, 'max' => 200],
    ];
}

/** @param list<array{birth_date?:?string}|int|null> $rows */
function census_age_stats(array $rows, array $bands): array
{
    $ages = [];
    foreach ($rows as $r) {
        if (is_int($r) || is_float($r)) {
            $ages[] = (int) $r;
            continue;
        }
        if (!is_array($r)) {
            continue;
        }
        if (isset($r['age']) && $r['age'] !== null && $r['age'] !== '') {
            $ages[] = (int) $r['age'];
            continue;
        }
        $age = age_from_birth($r['birth_date'] ?? null);
        if ($age !== null) {
            $ages[] = $age;
        }
    }
    $total = count($ages);
    $counts = [];
    foreach ($bands as $band) {
        $count = 0;
        foreach ($ages as $age) {
            if ($age >= $band['min'] && $age <= $band['max']) {
                $count++;
            }
        }
        $counts[] = $band + [
            'count' => $count,
            'pct' => $total ? ($count / $total) * 100 : 0.0,
        ];
    }
    $young = count(array_filter($ages, fn($a) => $a <= 14));
    $working = count(array_filter($ages, fn($a) => $a >= 15 && $a <= 64));
    $old = count(array_filter($ages, fn($a) => $a >= 65));
    sort($ages);
    $median = null;
    if ($ages) {
        $mid = intdiv(count($ages), 2);
        $median = count($ages) % 2 ? $ages[$mid] : ($ages[$mid - 1] + $ages[$mid]) / 2;
    }
    $ratio = static fn(int $part): float => $working ? ($part / $working) * 100 : 0.0;
    $max = 1;
    foreach ($counts as $c) {
        $max = max($max, (int) $c['count']);
    }
    return [
        'total' => $total,
        'counts' => $counts,
        'max' => $max,
        'youthDep' => $ratio($young),
        'oldDep' => $ratio($old),
        'totalDep' => $ratio($young + $old),
        'median' => $median,
    ];
}

function status_badge_style(?string $status): string
{
    $key = strtolower(trim((string) $status));
    if ($key === 'pending') {
        return 'background:#fef9c3;color:#92400e';
    }
    if ($key === 'in progress' || $key === 'approved') {
        return $key === 'approved'
            ? 'background:#dbeafe;color:#1e40af'
            : 'background:#dbeafe;color:#1e40af';
    }
    if (in_array($key, ['completed', 'active'], true)) {
        return 'background:#dcfce7;color:#166534';
    }
    return 'background:#fee2e2;color:#991b1b';
}

function admin_icon(string $name, int $size = 17): string
{
    $s = (string) $size;
    $icons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
        'registrations' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'registered' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/>',
        'census' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'requests' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'activity' => '<path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 14l2 2 4-4"/>',
        'certificates' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/>',
        'announcements' => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
        'officials' => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
        'website' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'admins' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>',
        'messages' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        'globe' => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'menu' => '<line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="18" x2="20" y2="18"/>',
        'x' => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'moon' => '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>',
        'refresh' => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
        'eye' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'plus' => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'printer' => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
        'trash' => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
        'pencil' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
        'history' => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>',
        'receipt' => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8H8"/><path d="M16 12H8"/><path d="M10 16H8"/>',
        'xcircle' => '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>',
        'bell' => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
        'search' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
    ];
    $path = $icons[$name] ?? $icons['dashboard'];
    return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $s . '" height="' . $s . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}

function issued_label(?string $when = null): string
{
    $ts = $when ? strtotime($when) : time();
    if (!$ts) {
        $ts = time();
    }
    $d = (int) date('j', $ts);
    $suf = ($d > 3 && $d < 21) ? 'th' : match ($d % 10) {
        1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th',
    };
    return $d . $suf . ' day of ' . strtoupper(date('F', $ts)) . ', ' . date('Y', $ts);
}

function issued_today(): string
{
    return issued_label();
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

/** A completed request is an issued certificate. Fill control and receipt numbers when they are missing. */
function record_certificate_issue(array $row): array
{
    if (($row['status'] ?? '') !== 'Completed') {
        return $row;
    }
    $last = [];
    if (!empty($row['last_cert'])) {
        $decoded = json_decode((string) $row['last_cert'], true);
        if (is_array($decoded)) {
            $last = $decoded;
        }
    }
    if (trim((string) ($last['controlNo'] ?? '')) !== '') {
        return $row;
    }

    static $captain = null;
    if ($captain === null) {
        $captainRow = db_one("SELECT name FROM officials WHERE position = 'Punong Barangay' LIMIT 1");
        $captain = strtoupper(trim((string) ($captainRow['name'] ?? 'PUNONG BARANGAY')));
    }

    $type = (string) ($row['certificate_type'] ?? '');
    $name = (string) ($row['resident_name'] ?? '');
    $address = 'Barangay Sta. Cruz, Bacnotan, La Union';
    $tpl = get_cert_template($type);
    $control = make_control_no();
    $when = (string) ($row['updated_at'] ?? '');
    $payload = [
        'type' => $type,
        'recipientName' => $name,
        'body' => fill_placeholders((string) ($tpl['body'] ?? ''), $name, $address),
        'purpose' => (string) (($row['purpose'] ?? '') !== '' ? $row['purpose'] : ($tpl['purpose'] ?? '')),
        'details' => (string) ($tpl['details'] ?? ''),
        'closing' => (string) ($tpl['closing'] ?? ''),
        'issuedDate' => issued_label($when !== '' ? $when : null),
        'certifiedBy' => $captain,
        'controlNo' => $control,
        'receiptNo' => 'OR-' . $control,
        'amount' => service_price(site(), $type),
    ];
    db_exec('UPDATE requests SET last_cert = ? WHERE id = ?', [json_encode($payload), (int) $row['id']]);
    try {
        db_exec(
            'INSERT INTO printed_certs (id, type, recipient_name, purpose, issued_date, certified_by, receipt_no, amount) VALUES (?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE type=VALUES(type), recipient_name=VALUES(recipient_name), purpose=VALUES(purpose), issued_date=VALUES(issued_date), certified_by=VALUES(certified_by), receipt_no=VALUES(receipt_no), amount=VALUES(amount)',
            [$control, $payload['type'], $payload['recipientName'], $payload['purpose'], $payload['issuedDate'], $payload['certifiedBy'], $payload['receiptNo'], $payload['amount']]
        );
    } catch (Throwable $e) {
        // The request record is already saved. Verification insert is optional if the table is unavailable.
    }
    $row['last_cert'] = json_encode($payload);
    return $row;
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

/** Parse website contact messages saved as Subject/Email/body. */
function parse_contact_message(string $raw): ?array
{
    if (!preg_match('/^Subject:\s*(.+)\REmail:\s*(.+)\R(?:\R)?([\s\S]*)$/u', trim($raw), $m)) {
        return null;
    }
    return [
        'subject' => trim($m[1]),
        'email' => trim($m[2]),
        'body' => trim($m[3]),
    ];
}

function route(): string
{
    $r = trim((string) ($_GET['r'] ?? ''), '/');
    return $r === '' ? 'home' : $r;
}

function ensure_announcements_schema(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $cols = array_column(db_all('SHOW COLUMNS FROM announcements'), 'Field');
        if (!in_array('cover_path', $cols, true)) {
            db_exec('ALTER TABLE announcements ADD COLUMN cover_path VARCHAR(255) NULL AFTER body');
        }
        if (!in_array('gallery_json', $cols, true)) {
            db_exec('ALTER TABLE announcements ADD COLUMN gallery_json TEXT NULL AFTER cover_path');
        }
        if (!in_array('featured', $cols, true)) {
            db_exec('ALTER TABLE announcements ADD COLUMN featured TINYINT(1) NOT NULL DEFAULT 0 AFTER gallery_json');
        }
        if (!in_array('updated_at', $cols, true)) {
            db_exec('ALTER TABLE announcements ADD COLUMN updated_at DATETIME NULL AFTER created_at');
        }
    } catch (Throwable $e) {
        // First install or locked permissions — schema.sql still defines the full table.
    }
}

/** @return list<string> */
function announcement_gallery(array $row): array
{
    $raw = $row['gallery_json'] ?? '';
    if ($raw === '' || $raw === null) {
        return [];
    }
    $decoded = json_decode((string) $raw, true);
    if (!is_array($decoded)) {
        return [];
    }
    return array_values(array_filter(array_map(static fn($p) => trim((string) $p), $decoded)));
}

function announcement_excerpt(array $row, int $len = 140): string
{
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($row['body'] ?? ''))) ?? '');
    if ($text === '') {
        return '';
    }
    if (mb_strlen($text) <= $len) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $len - 1)) . '…';
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
    // Compare expiry in MySQL (same clock as DATE_ADD(NOW()) used when creating OTPs).
    // Do NOT use PHP strtotime() — Hostinger MySQL is often UTC while PHP is Asia/Manila.
    $row = db_one(
        'SELECT * FROM email_otps
         WHERE email = ? AND purpose = ? AND consumed_at IS NULL AND expires_at > NOW()
         ORDER BY id DESC LIMIT 1',
        [$email, $purpose]
    );
    if (!$row) {
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
