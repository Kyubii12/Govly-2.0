<?php
if (current_user()) {
    redirect('dashboard');
}
$title = 'Register · GovLy 2.0';
require __DIR__ . '/_head.php';
require __DIR__ . '/_nav.php';
?>
<div class="auth-page">
  <form class="auth-card" style="width:min(560px,100%)" method="post" enctype="multipart/form-data">
    <h2>Create resident account</h2>
    <p class="text-muted">GovLy 2.0 verifies your <strong>email with OTP</strong>. Phone is optional contact only — <strong>no SMS</strong>.</p>
    <input type="hidden" name="action" value="register" />

    <div class="row g-2">
      <div class="col-md-6"><label class="form-label">First name</label><input class="form-control" name="first_name" required /></div>
      <div class="col-md-6"><label class="form-label">Last name</label><input class="form-control" name="last_name" required /></div>
    </div>
    <div class="mt-2"><label class="form-label">Middle name (optional)</label><input class="form-control" name="middle_name" /></div>
    <div class="mt-2"><label class="form-label">Email (OTP + certificate updates)</label><input class="form-control" type="email" name="email" required placeholder="you@email.com" /></div>
    <div class="mt-2"><label class="form-label">Mobile (contact only, optional)</label><input class="form-control" name="phone" placeholder="09XXXXXXXXX" /></div>
    <div class="mt-2"><label class="form-label">Street / house address</label><input class="form-control" name="street_address" required /></div>
    <div class="mt-2">
      <label class="form-label">Purok</label>
      <select class="form-select" name="purok" required>
        <option value="">Select purok</option>
        <?php foreach (puroks() as $p): ?><option><?= h($p) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="mt-2"><label class="form-label">Birthday</label><input class="form-control" type="date" name="birth_date" required /></div>
    <div class="row g-2 mt-1">
      <div class="col-md-6"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required minlength="8" /></div>
      <div class="col-md-6"><label class="form-label">Confirm password</label><input class="form-control" type="password" name="password2" required minlength="8" /></div>
    </div>
    <div class="mt-2"><label class="form-label">Valid ID photo</label><input class="form-control" type="file" name="id_photo" accept="image/*" required /></div>
    <div class="mt-2 mb-3"><label class="form-label">Selfie</label><input class="form-control" type="file" name="selfie" accept="image/*" required /></div>

    <button class="btn-govly" type="submit">Send OTP to Email</button>
    <a class="btn btn-outline-secondary w-100 mt-2" href="<?= h(base_url('login')) ?>">Already registered? Login</a>
  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= h(base_url('assets/govly2.js')) ?>?v=7"></script>
</body></html>
