<?php
require __DIR__ . '/app/bootstrap.php';

$r = route();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $action = post('action');
    try {
        require __DIR__ . '/app/actions.php';
        handle_action($action);
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
        redirect($r === 'home' ? '' : $r);
    }
}

$map = [
    'home' => 'home.php',
    'login' => 'login.php',
    'register' => 'register.php',
    'verify-otp' => 'verify_otp.php',
    'dashboard' => 'dashboard.php',
    'admin' => 'admin.php',
    'emergency' => 'emergency.php',
    'logout' => null,
    'verify' => 'verify.php',
    'print-cert' => 'print_cert.php',
];

if ($r === 'logout') {
    logout_user();
    flash('success', 'Signed out.');
    redirect('');
}

if (str_starts_with($r, 'verify/')) {
    $_GET['id'] = substr($r, 7);
    $r = 'verify';
}

$view = $map[$r] ?? 'home.php';
require __DIR__ . '/app/views/' . $view;
