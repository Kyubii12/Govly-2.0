<?php
declare(strict_types=1);

/**
 * Minimal SMTP mailer for Hostinger (ssl://smtp.hostinger.com:465).
 * Email only — no SMS.
 */
function mail_office_name(): string
{
    $c = $GLOBALS['CONFIG'] ?? [];
    $brgy = trim((string) ($c['barangay'] ?? 'Barangay Sta. Cruz'));
    $muni = trim((string) ($c['municipality'] ?? 'Bacnotan'));
    $prov = trim((string) ($c['province'] ?? 'La Union'));
    return $brgy . ', ' . $muni . ', ' . $prov;
}

function send_mail(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
{
    $m = $GLOBALS['CONFIG']['mail'] ?? [];
    if (empty($m['enabled'])) {
        error_log('[mail disabled] To: ' . $to . ' | ' . $subject);
        return false;
    }

    $host = $m['host'] ?? 'smtp.hostinger.com';
    $port = (int) ($m['port'] ?? 465);
    $secure = strtolower((string) ($m['secure'] ?? 'ssl'));
    $user = (string) ($m['username'] ?? '');
    $pass = (string) ($m['password'] ?? '');
    $from = (string) ($m['from'] ?? $user);
    $fromName = (string) ($m['from_name'] ?? mail_office_name());

    if ($user === '' || $pass === '' || str_contains($pass, 'YOUR_EMAIL')) {
        error_log('[mail] SMTP credentials not configured');
        return false;
    }

    if ($textBody === '') {
        $textBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)));
    }

    $remote = ($secure === 'ssl' ? 'ssl://' : '') . $host;
    $fp = @stream_socket_client($remote . ':' . $port, $errno, $errstr, 20);
    if (!$fp) {
        error_log("SMTP connect failed: $errstr ($errno)");
        return false;
    }
    stream_set_timeout($fp, 20);

    $expect = static function ($fp, array $codes) {
        $data = '';
        while ($line = fgets($fp, 515)) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $code = (int) substr($data, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new RuntimeException('SMTP unexpected reply: ' . trim($data));
        }
        return $data;
    };

    $cmd = static function ($fp, string $line) {
        fwrite($fp, $line . "\r\n");
    };

    try {
        $expect($fp, [220]);
        $cmd($fp, 'EHLO stacruzbacnotan.com');
        $expect($fp, [250]);

        if ($secure === 'tls') {
            $cmd($fp, 'STARTTLS');
            $expect($fp, [220]);
            stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $cmd($fp, 'EHLO stacruzbacnotan.com');
            $expect($fp, [250]);
        }

        $cmd($fp, 'AUTH LOGIN');
        $expect($fp, [334]);
        $cmd($fp, base64_encode($user));
        $expect($fp, [334]);
        $cmd($fp, base64_encode($pass));
        $expect($fp, [235]);

        $cmd($fp, 'MAIL FROM:<' . $from . '>');
        $expect($fp, [250]);
        $cmd($fp, 'RCPT TO:<' . $to . '>');
        $expect($fp, [250, 251]);
        $cmd($fp, 'DATA');
        $expect($fp, [354]);

        $boundary = 'b_' . bin2hex(random_bytes(8));
        $safeFrom = str_replace(['"', "\r", "\n"], '', $fromName);
        $headers = [
            'Date: ' . date('r'),
            'From: ' . sprintf('"%s" <%s>', $safeFrom, $from),
            'Reply-To: <' . $from . '>',
            'To: <' . $to . '>',
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];

        $body = implode("\r\n", $headers) . "\r\n\r\n";
        $body .= '--' . $boundary . "\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n" . $textBody . "\r\n";
        $body .= '--' . $boundary . "\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n" . $htmlBody . "\r\n";
        $body .= '--' . $boundary . "--\r\n.";

        $cmd($fp, $body);
        $expect($fp, [250]);
        $cmd($fp, 'QUIT');
        fclose($fp);
        return true;
    } catch (Throwable $e) {
        error_log('SMTP error: ' . $e->getMessage());
        if (is_resource($fp)) {
            fclose($fp);
        }
        return false;
    }
}

function mail_wrap(string $title, string $innerHtml): string
{
    $office = mail_office_name();
    $brgyLogo = base_url('assets/sta-cruz.png');
    $bagongLogo = base_url('assets/bagong-pilipinas-logo.png.png');
    return '<!DOCTYPE html><html><body style="font-family:Georgia,\'Times New Roman\',serif;background:#f4f6f9;padding:24px;margin:0;">'
        . '<div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #d7dee8;border-radius:8px;overflow:hidden;">'
        . '<div style="background:#fff;padding:16px 24px 12px;border-bottom:1px solid #e8eef5;text-align:center;">'
        . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;border-collapse:collapse;">'
        . '<tr>'
        . '<td style="padding:0 10px;vertical-align:middle;">'
        . '<img src="' . h($brgyLogo) . '" alt="Barangay Sta. Cruz" width="64" height="64" style="display:block;width:64px;height:64px;border-radius:50%;border:2px solid #e2e8f0;object-fit:cover;" />'
        . '</td>'
        . '<td style="padding:0 10px;vertical-align:middle;">'
        . '<img src="' . h($bagongLogo) . '" alt="Bagong Pilipinas" width="72" height="64" style="display:block;width:72px;height:64px;object-fit:contain;" />'
        . '</td>'
        . '</tr>'
        . '</table>'
        . '</div>'
        . '<div style="background:#0038A8;color:#fff;padding:18px 24px;">'
        . '<div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;opacity:.9;">Republic of the Philippines</div>'
        . '<div style="font-size:18px;font-weight:700;margin-top:4px;">' . h($office) . '</div>'
        . '<div style="font-size:13px;margin-top:6px;opacity:.95;">' . h($title) . '</div>'
        . '</div>'
        . '<div style="padding:24px;color:#1f2937;line-height:1.6;font-family:Segoe UI,Arial,sans-serif;font-size:15px;">'
        . $innerHtml
        . '</div>'
        . '<div style="padding:14px 24px;background:#f8fafc;color:#64748b;font-size:12px;font-family:Segoe UI,Arial,sans-serif;border-top:1px solid #e8eef5;">'
        . 'This message was sent by the Office of the Punong Barangay, ' . h($office) . '. '
        . 'Please do not reply with personal passwords. For assistance, visit the Barangay Hall during office hours.'
        . '</div>'
        . '</div></body></html>';
}

function send_otp_email(string $email, string $otp): bool
{
    $mins = (int) (($GLOBALS['CONFIG']['otp']['ttl_min'] ?? 10));
    $office = mail_office_name();
    $html = mail_wrap(
        'Email verification',
        '<p>Good day,</p>'
        . '<p>Thank you for registering with the online services of <strong>' . h($office) . '</strong>.</p>'
        . '<p>Your one-time verification code is:</p>'
        . '<p style="font-size:28px;font-weight:800;letter-spacing:6px;color:#0038A8;text-align:center;margin:20px 0;">' . h($otp) . '</p>'
        . '<p>This code expires in <strong>' . $mins . ' minutes</strong>. For your security, do not share it with anyone.</p>'
        . '<p>If you did not request this code, you may disregard this email.</p>'
        . '<p style="margin-top:22px;">Respectfully,<br><strong>Barangay Sta. Cruz</strong><br>Bacnotan, La Union</p>'
    );
    return send_mail($email, 'Barangay Sta. Cruz — verification code', $html);
}

function send_registration_status_email(array $user, string $status): bool
{
    if (empty($user['email'])) {
        return false;
    }
    $name = $user['first_name'] ?? 'Resident';
    $office = mail_office_name();
    if ($status === 'active' || $status === 'Approved') {
        $msg = '<p>We are pleased to inform you that your online registration with <strong>' . h($office) . '</strong> has been <strong>approved</strong>.</p>'
            . '<p>You may now sign in to request barangay certificates and related services.</p>';
        $subject = 'Barangay Sta. Cruz — registration approved';
        $title = 'Registration approved';
    } else {
        $msg = '<p>We regret to inform you that your online registration with <strong>' . h($office) . '</strong> was <strong>not approved</strong>.</p>'
            . '<p>Please visit the Barangay Hall for further assistance.</p>';
        $subject = 'Barangay Sta. Cruz — registration update';
        $title = 'Registration update';
    }
    $html = mail_wrap(
        $title,
        '<p>Dear ' . h($name) . ',</p>'
        . $msg
        . '<p style="margin-top:22px;">Respectfully,<br><strong>Office of the Punong Barangay</strong><br>Barangay Sta. Cruz, Bacnotan, La Union</p>'
    );
    return send_mail((string) $user['email'], $subject, $html);
}

function send_certificate_status_email(array $user, array $request): bool
{
    if (empty($user['email'])) {
        return false;
    }
    $status = $request['status'] ?? '';
    $type = $request['certificate_type'] ?? ($request['cert_type'] ?? 'Certificate');
    $note = trim((string) ($request['admin_note'] ?? ''));
    $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    if ($name === '') {
        $name = $request['resident_name'] ?? 'Resident';
    }
    $office = mail_office_name();

    $statusLine = match ($status) {
        'Approved' => 'Your request has been <strong>approved</strong>. Please wait for further instructions regarding printing and release.',
        'In Progress' => 'Your request is now <strong>in progress</strong> at the barangay office.',
        'Completed' => 'Your certificate has been <strong>printed and is ready for pickup</strong> at the Barangay Hall of Sta. Cruz. Payment, if any, is in cash at the office. Please bring a valid ID when claiming your document.',
        'Rejected' => 'Your request was <strong>not approved</strong>.' . ($note !== '' ? ' Note: ' . h($note) : ''),
        default => 'Your request status is now <strong>' . h($status) . '</strong>.',
    };

    $html = mail_wrap(
        'Certificate request update',
        '<p>Dear ' . h($name) . ',</p>'
        . '<p>This is an update regarding your <strong>' . h($type) . '</strong> request'
        . (!empty($request['id']) ? ' (Reference No. ' . (int) $request['id'] . ')' : '')
        . ' with <strong>' . h($office) . '</strong>:</p>'
        . '<p>' . $statusLine . '</p>'
        . '<p>You may also check your request status by signing in to the barangay online portal.</p>'
        . '<p style="margin-top:22px;">Respectfully,<br><strong>Office of the Punong Barangay</strong><br>Barangay Sta. Cruz, Bacnotan, La Union</p>'
    );

    return send_mail((string) $user['email'], 'Barangay Sta. Cruz — ' . $type . ' (' . $status . ')', $html);
}
