<?php
declare(strict_types=1);

/**
 * Minimal SMTP mailer for Hostinger (ssl://smtp.hostinger.com:465).
 * Email only — no SMS.
 */
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
    $fromName = (string) ($m['from_name'] ?? 'GovLy');

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
        $cmd($fp, 'EHLO govly.stacruz');
        $expect($fp, [250]);

        if ($secure === 'tls') {
            $cmd($fp, 'STARTTLS');
            $expect($fp, [220]);
            stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $cmd($fp, 'EHLO govly.stacruz');
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
        $headers = [
            'Date: ' . date('r'),
            'From: ' . sprintf('"%s" <%s>', addslashes($fromName), $from),
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
    return '<!DOCTYPE html><html><body style="font-family:Segoe UI,Arial,sans-serif;background:#f3f7fc;padding:24px;">'
        . '<div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #d6e3f3;border-radius:12px;overflow:hidden;">'
        . '<div style="background:linear-gradient(135deg,#0038A8,#002570);color:#fff;padding:18px 22px;font-weight:700;">'
        . h($title) . '</div>'
        . '<div style="padding:22px;color:#1f2937;line-height:1.55;">' . $innerHtml . '</div>'
        . '<div style="padding:14px 22px;background:#f8fafc;color:#64748b;font-size:12px;">Barangay Sta. Cruz, Bacnotan, La Union · GovLy 2.0 · Email only (no SMS)</div>'
        . '</div></body></html>';
}

function send_otp_email(string $email, string $otp): bool
{
    $mins = (int) (($GLOBALS['CONFIG']['otp']['ttl_min'] ?? 10));
    $html = mail_wrap(
        'Email verification code',
        '<p>Your one-time password (OTP) for <strong>GovLy 2.0</strong> registration is:</p>'
        . '<p style="font-size:28px;font-weight:800;letter-spacing:6px;color:#0038A8;">' . h($otp) . '</p>'
        . '<p>This code expires in <strong>' . $mins . ' minutes</strong>. Do not share it.</p>'
        . '<p>If you did not request this, ignore this email.</p>'
    );
    return send_mail($email, 'GovLy 2.0 OTP: ' . $otp, $html);
}

function send_registration_status_email(array $user, string $status): bool
{
    if (empty($user['email'])) {
        return false;
    }
    $name = $user['first_name'] ?? 'Resident';
    if ($status === 'active' || $status === 'Approved') {
        $msg = '<p>Your GovLy 2.0 registration was <strong>approved</strong>. You can now log in and request certificates.</p>';
        $subject = 'GovLy 2.0 registration approved';
    } else {
        $msg = '<p>Your GovLy 2.0 registration was <strong>rejected</strong>. Please visit the Barangay Hall for assistance.</p>';
        $subject = 'GovLy 2.0 registration update';
    }
    $html = mail_wrap('Registration update', '<p>Hello ' . h($name) . ',</p>' . $msg);
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

    $statusLine = match ($status) {
        'Approved' => 'Your request was <strong>approved</strong>. Please wait for printing and pickup instructions.',
        'In Progress' => 'Your request is now <strong>in progress</strong> at the barangay office.',
        'Completed' => 'Your request is <strong>done / ready for pickup</strong>. Please proceed to the Barangay Hall of Sta. Cruz and pay in cash if a fee applies.',
        'Rejected' => 'Your request was <strong>rejected</strong>.' . ($note !== '' ? ' Reason: ' . h($note) : ''),
        default => 'Your request status is now <strong>' . h($status) . '</strong>.',
    };

    $html = mail_wrap(
        'Certificate request update',
        '<p>Hello ' . h($name) . ',</p>'
        . '<p>Update for your <strong>' . h($type) . '</strong> request'
        . (!empty($request['id']) ? ' (#' . (int) $request['id'] . ')' : '') . ':</p>'
        . '<p>' . $statusLine . '</p>'
        . '<p>You can also check status anytime by logging into GovLy 2.0. This system uses <strong>email only</strong> (no SMS).</p>'
    );

    return send_mail((string) $user['email'], 'GovLy 2.0: ' . $type . ' is ' . $status, $html);
}
