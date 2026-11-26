<?php
if (current_user()) {
    redirect(is_admin_user(current_user()) ? 'admin' : 'dashboard');
}
$title = 'Login · GovLy 2.0';
$role = ($_GET['role'] ?? 'resident') === 'admin' ? 'admin' : 'resident';
require __DIR__ . '/_head.php';
require __DIR__ . '/_nav.php';
?>
<div class="auth-page">
  <form class="auth-card" method="post">
    <div>
      <h2>Barangay Sta. Cruz</h2>
      <p class="text-muted mb-0">GovLy 2.0 — login with <strong>email</strong> and password. No SMS.</p>
    </div>

    <div class="role-tabs">
      <button type="button" class="<?= $role === 'resident' ? 'active' : '' ?>" onclick="location.href='<?= h(base_url('login?role=resident')) ?>'">Resident</button>
      <button type="button" class="<?= $role === 'admin' ? 'active' : '' ?>" onclick="location.href='<?= h(base_url('login?role=admin')) ?>'">Admin</button>
    </div>

    <input type="hidden" name="action" value="login" />
    <input type="hidden" name="expected_role" value="<?= h($role) ?>" />

    <div class="mb-3">
      <label class="form-label fw-semibold">Email Address</label>
      <input class="form-control" type="email" name="phone" required placeholder="you@email.com"
        value="<?= $role === 'admin' ? 'admin@stacruzbacnotan.com' : '' ?>" />
      <div class="form-text">Residents and admins both use email (OTP registration is email-only).</div>
    </div>
    <div class="mb-3">
      <label class="form-label fw-semibold">Password</label>
      <input class="form-control" type="password" name="password" required placeholder="••••••••" />
    </div>
    <button class="btn-govly mb-2" type="submit">Login</button>
    <a class="btn btn-outline-secondary w-100" href="<?= h(base_url()) ?>">← Back to Home</a>
    <?php if ($role === 'resident'): ?>
      <p class="text-center mt-3 mb-0"><a href="<?= h(base_url('register')) ?>">Create a resident account</a></p>
    <?php endif; ?>
  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= h(base_url('assets/govly2.js')) ?>?v=11"></script>
</body></html>
