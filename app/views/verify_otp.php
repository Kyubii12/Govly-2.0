<?php
$email = (string) ($_SESSION['pending_register_email'] ?? '');
if ($email === '') {
    flash('error', 'Start registration first.');
    redirect('register');
}
$title = 'Verify OTP · GovLy 2.0';
require __DIR__ . '/_head.php';
require __DIR__ . '/_nav.php';
?>
<div class="auth-page">
  <form class="auth-card" method="post">
    <h2>Enter email OTP</h2>
    <p class="text-muted">Barangay Sta. Cruz sent a 6-digit code to <strong><?= h($email) ?></strong>. Expires in 10 minutes.</p>
    <input type="hidden" name="action" value="verify_otp" />
    <label class="form-label">OTP code</label>
    <input class="form-control text-center fw-bold fs-3" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus placeholder="123456" />
    <button class="btn-govly mt-3" type="submit">Verify &amp; Submit Registration</button>
    <a class="btn btn-outline-secondary w-100 mt-2" href="<?= h(base_url('register')) ?>">Change details</a>
  </form>
  <form method="post" class="mt-3 text-center">
    <input type="hidden" name="action" value="resend_otp" />
    <button class="btn btn-link" type="submit">Resend OTP email</button>
  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= h(base_url('assets/govly2.js')) ?>?v=7"></script>
</body></html>
