<?php
declare(strict_types=1);

function handle_action(string $action): void
{
    match ($action) {
        'login' => act_login(),
        'register' => act_register(),
        'verify_otp' => act_verify_otp(),
        'resend_otp' => act_resend_otp(),
        'request_cert' => act_request_cert(),
        'send_message' => act_send_message(),
        'approve_reg' => act_approve_reg(),
        'reject_reg' => act_reject_reg(),
        'save_resident' => act_save_resident(),
        'delete_resident' => act_delete_resident(),
        'authorize_resident' => act_authorize_resident(),
        'update_request' => act_update_request(),
        'walkin_print' => act_walkin_print(),
        'print_cert' => act_print_cert(),
        'save_template' => act_save_template(),
        'add_template' => act_add_template(),
        'delete_template' => act_delete_template(),
        'save_announcement' => act_save_announcement(),
        'delete_announcement' => act_delete_announcement(),
        'save_official' => act_save_official(),
        'delete_official' => act_delete_official(),
        'save_site' => act_save_site(),
        'save_admin' => act_save_admin(),
        'delete_admin' => act_delete_admin(),
        'reply_message' => act_reply_message(),
        'contact' => act_contact(),
        default => throw new RuntimeException('Unknown action.'),
    };
}

function act_login(): void
{
    $ident = strtolower(post('phone'));
    $password = (string) ($_POST['password'] ?? '');
    $expected = post('expected_role') ?: 'resident';
    $phone = normalize_phone($ident);
    $user = db_one(
        'SELECT * FROM users WHERE email = ? OR phone = ? OR phone = ? LIMIT 1',
        [$ident, $phone, post('phone')]
    );
    if (!$user || !password_verify($password, $user['password_hash'])) {
        throw new RuntimeException('Incorrect email or password.');
    }
    if ($user['status'] !== 'active') {
        throw new RuntimeException('This account is not active yet. Wait for admin approval after email OTP verification.');
    }
    if ($expected === 'admin' && !is_admin_user($user)) {
        throw new RuntimeException('This is not an admin account. Use the Resident tab.');
    }
    if ($expected === 'resident' && is_admin_user($user)) {
        throw new RuntimeException('Admin accounts should use the Admin tab.');
    }
    login_user($user);
    log_activity('Login', $user['email'] ?: $user['phone']);
    redirect(is_admin_user($user) ? 'admin' : 'dashboard');
}

function act_register(): void
{
    $first = post('first_name');
    $middle = post('middle_name');
    $last = post('last_name');
    $email = strtolower(post('email'));
    $phoneRaw = post('phone');
    $phone = $phoneRaw !== '' ? normalize_phone($phoneRaw) : null;
    $password = (string) ($_POST['password'] ?? '');
    $password2 = (string) ($_POST['password2'] ?? '');
    $purok = post('purok');
    $address = post('street_address');
    $birth = post('birth_date');

    if ($first === '' || $last === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $purok === '') {
        throw new RuntimeException('Complete all required fields with a valid email.');
    }
    if (strlen($password) < 8 || $password !== $password2) {
        throw new RuntimeException('Passwords must match and be at least 8 characters.');
    }
    if (db_one('SELECT id FROM users WHERE email = ?', [$email])) {
        throw new RuntimeException('That email is already registered.');
    }
    if ($phone && db_one('SELECT id FROM users WHERE phone = ?', [$phone])) {
        throw new RuntimeException('That mobile number is already registered.');
    }

    $idFile = save_upload('id_photo', __DIR__ . '/../uploads/ids');
    $selfieFile = save_upload('selfie', __DIR__ . '/../uploads/selfies');
    if (!$idFile || !$selfieFile) {
        throw new RuntimeException('Valid ID and selfie must be JPG/PNG/WebP under 5MB.');
    }

    $_SESSION['pending_register'] = [
        'first_name' => $first,
        'middle_name' => $middle,
        'last_name' => $last,
        'email' => $email,
        'phone' => $phone,
        'street_address' => $address,
        'purok' => $purok,
        'birth_date' => $birth,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'id_path' => 'uploads/ids/' . $idFile,
        'selfie_path' => 'uploads/selfies/' . $selfieFile,
    ];
    $_SESSION['pending_register_email'] = $email;

    $otp = create_email_otp($email, 'register');
    $sent = send_otp_email($email, $otp);
    if (!$sent) {
        flash('error', 'Could not send OTP email. Check SMTP settings in app/config.php, then try again.');
        redirect('register');
    }
    flash('success', 'OTP sent to your email. Enter the code to finish registration.');
    redirect('verify-otp');
}

function act_verify_otp(): void
{
    $email = (string) ($_SESSION['pending_register_email'] ?? '');
    $data = $_SESSION['pending_register'] ?? null;
    $otp = preg_replace('/\D+/', '', post('otp')) ?? '';

    if ($email === '' || !is_array($data)) {
        throw new RuntimeException('Registration session expired. Please register again.');
    }
    if (!verify_email_otp($email, $otp, 'register')) {
        throw new RuntimeException('Invalid or expired OTP.');
    }

    $uid = db_insert(
        'INSERT INTO users (first_name, middle_name, last_name, phone, email, password_hash, role, status, street_address, purok, id_path, selfie_path)
         VALUES (?,?,?,?,?,?,\'resident\',\'pending\',?,?,?,?)',
        [
            $data['first_name'], $data['middle_name'] ?: null, $data['last_name'],
            $data['phone'], $data['email'], $data['password_hash'],
            $data['street_address'], $data['purok'], $data['id_path'], $data['selfie_path'],
        ]
    );
    db_insert(
        'INSERT INTO registrations (user_id, first_name, middle_name, last_name, phone, email, street_address, purok, birth_date, status)
         VALUES (?,?,?,?,?,?,?,?,?,\'Pending\')',
        [
            $uid, $data['first_name'], $data['middle_name'] ?: null, $data['last_name'],
            $data['phone'], $data['email'], $data['street_address'], $data['purok'],
            $data['birth_date'] ?: null,
        ]
    );

    unset($_SESSION['pending_register'], $_SESSION['pending_register_email']);
    log_activity('Registration OTP verified', $email);
    flash('success', 'Email verified. Your registration is pending admin approval. Certificate updates will go to this email (no SMS).');
    redirect('login');
}

function act_resend_otp(): void
{
    $email = (string) ($_SESSION['pending_register_email'] ?? '');
    if ($email === '' || empty($_SESSION['pending_register'])) {
        throw new RuntimeException('Start registration first.');
    }
    $otp = create_email_otp($email, 'register');
    if (!send_otp_email($email, $otp)) {
        throw new RuntimeException('Could not resend OTP. Check SMTP settings.');
    }
    flash('success', 'A new OTP was sent to your email.');
    redirect('verify-otp');
}

function save_upload(string $field, string $dir): ?string
{
    if (empty($_FILES[$field]['tmp_name']) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
        return null;
    }
    if (($_FILES[$field]['size'] ?? 0) > 5 * 1024 * 1024) {
        return null;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES[$field]['tmp_name']);
    $map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($map[$mime])) {
        return null;
    }
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $name = bin2hex(random_bytes(8)) . '.' . $map[$mime];
    $dest = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $name;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
        return null;
    }
    return $name;
}

function act_request_cert(): void
{
    $u = require_login();
    if (!is_active_resident($u)) {
        throw new RuntimeException('Only Active registered residents can request a certificate.');
    }
    $type = post('certificate_type');
    $purpose = post('purpose');
    if (!$type || !$purpose) {
        throw new RuntimeException('Choose a certificate and enter a purpose.');
    }
    $name = full_name($u);
    $id = db_insert(
        'INSERT INTO requests (user_id, resident_name, phone, email, certificate_type, payment_mode, purpose, status)
         VALUES (?,?,?,?,?,?,?,\'Pending\')',
        [$u['id'], $name, $u['phone'], $u['email'], $type, post('payment_mode') ?: 'Pay at Barangay Hall', $purpose]
    );
    db_insert('INSERT INTO request_logs (request_id, action, detail, actor) VALUES (?,?,?,?)', [
        $id, 'Submitted', $type, $name,
    ]);
    flash('success', 'Request submitted. You will receive an email when status changes.');
    redirect('dashboard');
}

function act_send_message(): void
{
    $u = require_login();
    $msg = post('message');
    if ($msg === '') {
        throw new RuntimeException('Type a message.');
    }
    $name = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
    db_insert(
        'INSERT INTO messages (sender_id, sender_name, sender_role, recipient_role, message) VALUES (?,?,?,?,?)',
        [$u['id'], $name, $u['role'], is_admin_user($u) ? 'resident' : 'admin', $msg]
    );
    flash('success', 'Message sent.');
    redirect(is_admin_user($u) ? 'admin?tab=messages' : 'dashboard');
}

function act_approve_reg(): void
{
    require_admin();
    $id = (int) post('id');
    $reg = db_one('SELECT * FROM registrations WHERE id = ?', [$id]);
    if (!$reg) {
        throw new RuntimeException('Registration not found.');
    }
    db_exec("UPDATE registrations SET status = 'Approved' WHERE id = ?", [$id]);
    if ($reg['user_id']) {
        db_exec("UPDATE users SET status = 'active' WHERE id = ?", [$reg['user_id']]);
    }
    $name = trim($reg['first_name'] . ' ' . ($reg['middle_name'] ?? '') . ' ' . $reg['last_name']);
    $existing = db_one('SELECT id FROM residents WHERE phone = ? LIMIT 1', [$reg['phone']]);
    if ($existing) {
        db_exec("UPDATE residents SET user_id = ?, full_name = ?, status = 'Active', authorized = 1, birth_date = COALESCE(?, birth_date) WHERE id = ?", [
            $reg['user_id'], $name, $reg['birth_date'] ?: null, $existing['id'],
        ]);
    } else {
        db_insert(
            "INSERT INTO residents (user_id, full_name, address, phone, purok, birth_date, status, authorized) VALUES (?,?,?,?,?,?, 'Active', 1)",
            [$reg['user_id'], $name, $reg['street_address'], $reg['phone'], $reg['purok'], $reg['birth_date'] ?: null]
        );
    }
    $user = $reg['user_id'] ? db_one('SELECT * FROM users WHERE id = ?', [$reg['user_id']]) : null;
    if ($user) {
        send_registration_status_email($user, 'active');
    }
    log_activity('Approved registration', $name);
    flash('success', 'Registration approved. Approval email sent if SMTP is configured.');
    redirect('admin?tab=registrations');
}

function act_reject_reg(): void
{
    require_admin();
    $id = (int) post('id');
    $reg = db_one('SELECT * FROM registrations WHERE id = ?', [$id]);
    db_exec("UPDATE registrations SET status = 'Rejected' WHERE id = ?", [$id]);
    if ($reg && $reg['user_id']) {
        db_exec("UPDATE users SET status = 'rejected' WHERE id = ?", [$reg['user_id']]);
        $user = db_one('SELECT * FROM users WHERE id = ?', [$reg['user_id']]);
        if ($user) {
            send_registration_status_email($user, 'rejected');
        }
    }
    log_activity('Rejected registration', (string) $id);
    flash('success', 'Registration rejected.');
    redirect('admin?tab=registrations');
}

function act_save_resident(): void
{
    require_admin();
    $id = (int) post('id');
    $name = preg_replace('/\s+/', ' ', trim(post('first_name') . ' ' . post('middle_name') . ' ' . post('last_name')));
    $fields = [$name, post('address'), post('phone'), post('purok'), post('occupation'), post('birth_date') ?: null];
    if ($id) {
        db_exec('UPDATE residents SET full_name=?, address=?, phone=?, purok=?, occupation=?, birth_date=? WHERE id=?', [...$fields, $id]);
        flash('success', 'Census record updated.');
    } else {
        db_insert("INSERT INTO residents (full_name, address, phone, purok, occupation, birth_date, status) VALUES (?,?,?,?,?,?, 'Listed')", $fields);
        flash('success', 'Census resident added.');
    }
    log_activity('Saved census', $name);
    redirect('admin?tab=census');
}

function act_delete_resident(): void
{
    require_admin();
    db_exec('DELETE FROM residents WHERE id = ?', [(int) post('id')]);
    log_activity('Deleted census', (string) post('id'));
    flash('success', 'Resident removed from census.');
    redirect('admin?tab=census');
}

function act_authorize_resident(): void
{
    require_admin();
    $id = (int) post('id');
    $row = db_one('SELECT * FROM residents WHERE id = ?', [$id]);
    if (!$row) {
        throw new RuntimeException('Resident not found.');
    }
    $mode = post('mode');
    if ($mode === 'create') {
        $phone = normalize_phone(post('phone') ?: ($row['phone'] ?? ''));
        $email = strtolower(post('email'));
        $password = (string) ($_POST['password'] ?? '');
        if (strlen($password) < 8) {
            throw new RuntimeException('Password must be at least 8 characters.');
        }
        $parts = preg_split('/\s+/', trim($row['full_name']));
        $first = $parts[0] ?? 'Resident';
        $last = count($parts) > 1 ? $parts[count($parts) - 1] : '';
        $uid = db_insert(
            "INSERT INTO users (first_name, last_name, phone, email, password_hash, role, status, street_address, purok) VALUES (?,?,?,?,?, 'resident', 'active', ?, ?)",
            [$first, $last, $phone, $email ?: null, password_hash($password, PASSWORD_DEFAULT), $row['address'], $row['purok']]
        );
        db_exec("UPDATE residents SET user_id = ?, phone = ?, status = 'Active', authorized = 1 WHERE id = ?", [$uid, $phone, $id]);
        if ($email) {
            send_mail($email, 'GovLy account ready', mail_wrap(
                'Account authorized',
                '<p>Hello ' . h($first) . ',</p><p>Your GovLy resident account is active. Log in with your email/mobile and the password given by the barangay office.</p>'
            ));
        }
    } else {
        $phone = normalize_phone(post('phone'));
        $user = db_one("SELECT * FROM users WHERE phone = ? AND role = 'resident'", [$phone]);
        if (!$user) {
            throw new RuntimeException('No website account found for that number.');
        }
        db_exec("UPDATE users SET status = 'active' WHERE id = ?", [$user['id']]);
        db_exec("UPDATE residents SET user_id = ?, status = 'Active', authorized = 1 WHERE id = ?", [$user['id'], $id]);
    }
    flash('success', 'Resident is now Active.');
    redirect('admin?tab=census');
}

function act_update_request(): void
{
    require_admin();
    $id = (int) post('id');
    $status = post('status');
    $note = post('admin_note');
    if (!in_array($status, ['Pending', 'Approved', 'In Progress', 'Completed', 'Rejected'], true)) {
        throw new RuntimeException('Invalid status.');
    }
    db_exec('UPDATE requests SET status = ?, admin_note = ?, updated_at = NOW() WHERE id = ?', [
        $status, $note !== '' ? $note : null, $id,
    ]);
    db_insert('INSERT INTO request_logs (request_id, action, detail, actor) VALUES (?,?,?,?)', [
        $id, 'Status: ' . $status, $note, current_user()['email'] ?? 'admin',
    ]);

    $row = db_one('SELECT * FROM requests WHERE id = ?', [$id]);
    if ($row && $status === 'Completed') {
        $row = record_certificate_issue($row);
    }
    // Email only when certificate is Completed (ready) or Rejected — no SMS
    if ($row && in_array($status, ['Completed', 'Rejected'], true)) {
        $user = [
            'first_name' => explode(' ', (string) $row['resident_name'])[0] ?? 'Resident',
            'last_name' => '',
            'email' => $row['email'],
        ];
        if (!$user['email'] && $row['user_id']) {
            $u = db_one('SELECT * FROM users WHERE id = ?', [$row['user_id']]);
            if ($u) {
                $user = $u;
            }
        }
        if (!empty($user['email'])) {
            $sent = send_certificate_status_email($user, $row);
            if ($sent) {
                db_exec('UPDATE requests SET status_email_sent_at = NOW() WHERE id = ?', [$id]);
            }
            flash('success', 'Request updated' . ($sent ? ' and email notification sent.' : '. Email could not be sent — check SMTP.'));
        } else {
            flash('success', 'Request updated (no email on file).');
        }
    } else {
        flash('success', 'Request updated.');
    }
    log_activity('Updated request', '#' . $id . ' → ' . $status);
    redirect('admin?tab=requests');
}

function act_walkin_print(): void
{
    require_admin();
    $rid = (int) post('resident_id');
    $res = db_one('SELECT * FROM residents WHERE id = ?', [$rid]);
    if (!$res) {
        throw new RuntimeException('Resident not found.');
    }
    $type = post('certificate_type');
    $id = db_insert(
        "INSERT INTO requests (resident_id, resident_name, phone, certificate_type, payment_mode, purpose, status, walk_in) VALUES (?,?,?,?, 'Walk-in', ?, 'Completed', 1)",
        [$rid, $res['full_name'], $res['phone'], $type, post('purpose') ?: 'Walk-in']
    );
    redirect('print-cert?id=' . $id);
}

function act_print_cert(): void
{
    require_admin();
    $id = (int) post('id');
    $control = post('control_no') ?: make_control_no();
    $payload = [
        'type' => post('type'),
        'recipientName' => post('recipient_name'),
        'body' => post('body'),
        'purpose' => post('purpose'),
        'details' => post('details'),
        'closing' => post('closing'),
        'issuedDate' => post('issued_date'),
        'certifiedBy' => post('certified_by'),
        'controlNo' => $control,
    ];
    $prev = db_one('SELECT status, email, user_id, resident_name, certificate_type FROM requests WHERE id=?', [$id]);
    $newStatus = post('mark_completed') === '1' ? 'Completed' : ($prev['status'] ?? 'In Progress');
    db_exec('UPDATE requests SET last_cert = ?, status = ?, updated_at = NOW() WHERE id = ?', [
        json_encode($payload), $newStatus, $id,
    ]);
    db_exec(
        'INSERT INTO printed_certs (id, type, recipient_name, purpose, issued_date, certified_by, receipt_no, amount) VALUES (?,?,?,?,?,?,?,?)
         ON DUPLICATE KEY UPDATE type=VALUES(type), recipient_name=VALUES(recipient_name), purpose=VALUES(purpose), issued_date=VALUES(issued_date), certified_by=VALUES(certified_by), receipt_no=VALUES(receipt_no), amount=VALUES(amount)',
        [$control, $payload['type'], $payload['recipientName'], $payload['purpose'], $payload['issuedDate'], $payload['certifiedBy'], post('receipt_no'), post('amount')]
    );
    db_insert('INSERT INTO request_logs (request_id, action, detail, actor) VALUES (?,?,?,?)', [
        $id, 'Printed', $payload['type'] . ' printed', current_user()['email'] ?? 'admin',
    ]);

    if ($newStatus === 'Completed' && $prev) {
        $email = $prev['email'];
        $user = $prev['user_id'] ? db_one('SELECT * FROM users WHERE id = ?', [$prev['user_id']]) : null;
        if (!$email && $user) {
            $email = $user['email'];
        }
        if ($email) {
            send_certificate_status_email(
                $user ?: ['first_name' => $prev['resident_name'], 'email' => $email],
                ['id' => $id, 'status' => 'Completed', 'certificate_type' => $prev['certificate_type'] ?? $payload['type']]
            );
            db_exec('UPDATE requests SET status_email_sent_at = NOW() WHERE id = ?', [$id]);
        }
    }

    log_activity('Printed certificate', $control);
    redirect('print-cert?id=' . $id . '&ok=1');
}

function act_save_template(): void
{
    require_admin();
    db_exec(
        'INSERT INTO cert_templates (type, body, purpose, details, closing) VALUES (?,?,?,?,?)
         ON DUPLICATE KEY UPDATE body=VALUES(body), purpose=VALUES(purpose), details=VALUES(details), closing=VALUES(closing)',
        [post('type'), post('body'), post('purpose'), post('details'), post('closing')]
    );
    flash('success', 'Template saved.');
    redirect('admin?tab=certificates&type=' . urlencode(post('type')));
}

function act_add_template(): void
{
    require_admin();
    $type = post('type');
    if (!$type) {
        throw new RuntimeException('Enter a certificate name.');
    }
    db_exec(
        'INSERT IGNORE INTO cert_templates (type, body, closing) VALUES (?,?,?)',
        [$type, 'This is to certify that RESIDENT NAME, of legal age, is a bona fide resident of ADDRESS.', 'This certification is issued upon the request of the party concerned for intents and purposes it may serve.']
    );
    flash('success', 'Template added.');
    redirect('admin?tab=certificates&type=' . urlencode($type));
}

function act_delete_template(): void
{
    require_admin();
    db_exec('DELETE FROM cert_templates WHERE type = ?', [post('type')]);
    flash('success', 'Template removed.');
    redirect('admin?tab=certificates');
}

function act_save_announcement(): void
{
    require_admin();
    db_insert('INSERT INTO announcements (title, body) VALUES (?,?)', [post('title'), post('body')]);
    log_activity('Posted announcement', post('title'));
    flash('success', 'Announcement posted.');
    redirect('admin?tab=announcements');
}

function act_delete_announcement(): void
{
    require_admin();
    db_exec('DELETE FROM announcements WHERE id = ?', [(int) post('id')]);
    flash('success', 'Announcement removed.');
    redirect('admin?tab=announcements');
}

function act_save_official(): void
{
    require_admin();
    $photo = null;
    if (!empty($_FILES['photo']['tmp_name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new RuntimeException('Use a JPG or PNG photo.');
        }
        $name = 'off_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
        $destDir = dirname(__DIR__) . '/uploads/officials';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        move_uploaded_file($_FILES['photo']['tmp_name'], $destDir . '/' . $name);
        $photo = 'uploads/officials/' . $name;
    }
    $id = (int) post('id');
    $order = post('position') === 'Kagawad' ? (int) post('order_num') : 99;
    if ($id) {
        if ($photo) {
            db_exec('UPDATE officials SET name=?, position=?, description=?, order_num=?, photo_url=? WHERE id=?', [
                post('name'), post('position'), post('description'), $order, $photo, $id,
            ]);
        } else {
            db_exec('UPDATE officials SET name=?, position=?, description=?, order_num=? WHERE id=?', [
                post('name'), post('position'), post('description'), $order, $id,
            ]);
        }
    } else {
        if (!$photo) {
            throw new RuntimeException('Upload a profile photo.');
        }
        db_insert('INSERT INTO officials (name, position, description, order_num, photo_url) VALUES (?,?,?,?,?)', [
            post('name'), post('position'), post('description'), $order, $photo,
        ]);
    }
    flash('success', 'Official saved.');
    redirect('admin?tab=officials');
}

function act_delete_official(): void
{
    require_admin();
    db_exec('DELETE FROM officials WHERE id = ?', [(int) post('id')]);
    flash('success', 'Official removed.');
    redirect('admin?tab=officials');
}

function act_save_site(): void
{
    require_admin();
    $site = site();
    $site['emergencyCall'] = post('emergencyCall');
    $site['emergencyLead'] = post('emergencyLead');
    $site['officeAddress'] = post('officeAddress');
    $site['contactPhones'] = post('contactPhones');
    $site['contactEmail'] = post('contactEmail');
    $site['officeHours'] = post('officeHours');
    $site['footerLocation'] = post('footerLocation');
    $prices = $_POST['price'] ?? [];
    $times = $_POST['time'] ?? [];
    $descs = $_POST['desc'] ?? [];
    foreach ($site['services'] ?? [] as $i => $s) {
        if (isset($prices[$i])) {
            $site['services'][$i]['price'] = $prices[$i];
        }
        if (isset($times[$i])) {
            $site['services'][$i]['time'] = $times[$i];
        }
        if (isset($descs[$i])) {
            $site['services'][$i]['desc'] = $descs[$i];
        }
    }
    save_site($site);
    log_activity('Updated website settings', '');
    flash('success', 'Website content saved.');
    redirect('admin?tab=website');
}

function act_save_admin(): void
{
    $me = require_admin();
    if (($me['role'] ?? '') !== 'super_admin') {
        throw new RuntimeException('Only Super Admin can create admin accounts.');
    }
    $count = (int) (db_one("SELECT COUNT(*) c FROM users WHERE role IN ('admin','super_admin')")['c'] ?? 0);
    if ($count >= 3) {
        throw new RuntimeException('Maximum of 3 admin accounts reached.');
    }
    $phone = normalize_phone(post('phone'));
    $password = (string) ($_POST['password'] ?? '');
    if (strlen($password) < 8) {
        throw new RuntimeException('Password must be at least 8 characters.');
    }
    db_insert(
        "INSERT INTO users (first_name, last_name, phone, email, password_hash, role, status) VALUES (?,?,?,?,?, 'admin', 'active')",
        [post('first_name'), post('last_name'), $phone, post('email') ?: null, password_hash($password, PASSWORD_DEFAULT)]
    );
    log_activity('Created admin', post('first_name') . ' ' . post('last_name'));
    flash('success', 'Admin account created.');
    redirect('admin?tab=admins');
}

function act_delete_admin(): void
{
    $me = require_admin();
    if (($me['role'] ?? '') !== 'super_admin') {
        throw new RuntimeException('Only Super Admin can delete admins.');
    }
    $id = (int) post('id');
    $target = db_one('SELECT * FROM users WHERE id = ?', [$id]);
    if (!$target || !is_admin_user($target)) {
        throw new RuntimeException('Admin not found.');
    }
    if (($target['role'] ?? '') === 'super_admin') {
        throw new RuntimeException('Cannot delete Super Admin.');
    }
    $count = (int) (db_one("SELECT COUNT(*) c FROM users WHERE role IN ('admin','super_admin')")['c'] ?? 0);
    if ($count <= 1) {
        throw new RuntimeException('Keep at least one admin account.');
    }
    db_exec('DELETE FROM users WHERE id = ?', [$id]);
    flash('success', 'Admin removed.');
    redirect('admin?tab=admins');
}

function act_reply_message(): void
{
    require_admin();
    db_insert(
        "INSERT INTO messages (sender_id, sender_name, sender_role, recipient_role, message) VALUES (?,?, 'admin', 'resident', ?)",
        [current_user()['id'], 'Admin', post('message')]
    );
    flash('success', 'Reply sent.');
    redirect('admin?tab=messages');
}

function act_contact(): void
{
    flash('success', 'Message received. The barangay will contact you soon.');
    redirect('');
}
