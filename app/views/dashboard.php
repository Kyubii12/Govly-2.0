<?php
$u = require_login();
if (is_admin_user($u)) {
    redirect('admin');
}
$title = 'My Requests — GovLy 2.0';
$active = is_active_resident($u);
$reqs = db_all('SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC', [$u['id']]);
$msgs = db_all(
    'SELECT * FROM messages WHERE sender_id = ? OR (sender_role = "admin" AND recipient_role = "resident") ORDER BY created_at DESC LIMIT 20',
    [$u['id']]
);
require __DIR__ . '/_head.php';
require __DIR__ . '/_nav.php';
?>
<div class="container-xl px-3 py-4">
  <h1 style="color:#003d82">Hello, <?= h($u['first_name']) ?></h1>
  <p class="text-muted">
    GovLy 2.0 account:
    <span class="badge <?= $active ? 'active' : 'pending' ?>"><?= $active ? 'Active' : 'Pending approval' ?></span>
    · Completion emails go to <strong><?= h($u['email'] ?: 'no email') ?></strong> (no SMS)
  </p>

  <?php if (!$active): ?>
    <div class="card-soft"><p class="mb-0">You can request certificates after the barangay approves your registration.</p></div>
  <?php else: ?>
    <div class="row g-3">
      <div class="col-lg-7">
        <form class="card-soft" method="post">
          <h3 class="h5 fw-bold" style="color:#003d82">New certificate request</h3>
          <input type="hidden" name="action" value="request_cert" />
          <div class="mb-2"><label class="form-label">Certificate type</label>
            <select class="form-select" name="certificate_type" required>
              <?php foreach (cert_types() as $t): ?><option><?= h($t) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2"><label class="form-label">Purpose</label>
            <textarea class="form-control" name="purpose" rows="3" required placeholder="Employment, scholarship, medical assistance…"></textarea>
          </div>
          <div class="mb-3"><label class="form-label">Payment</label>
            <select class="form-select" name="payment_mode">
              <option>Pay at Barangay Hall</option>
              <option>Cash</option>
              <option>GCash</option>
            </select>
          </div>
          <button class="btn btn-warning fw-bold" type="submit">Submit request</button>
        </form>
      </div>
      <div class="col-lg-5">
        <div class="card-soft">
          <h3 class="h5 fw-bold" style="color:#003d82">Pickup reminder</h3>
          <p class="text-muted mb-0">Payment is cash at the Barangay Hall. When admin marks your request <strong>Completed</strong>, GovLy 2.0 emails you. No SMS.</p>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <h2 class="mt-4" style="color:#003d82">My requests</h2>
  <div class="table-wrap">
    <table class="data">
      <thead>
        <tr><th>#</th><th>Type</th><th>Purpose</th><th>Status</th><th>Updated</th></tr>
      </thead>
      <tbody>
        <?php if (!$reqs): ?><tr><td colspan="5" class="muted">No requests yet.</td></tr><?php endif; ?>
        <?php foreach ($reqs as $r): ?>
          <tr>
            <td><?= (int) $r['id'] ?></td>
            <td><?= h($r['certificate_type']) ?></td>
            <td><?= h($r['purpose']) ?></td>
            <td><span class="badge badge-<?= h(str_replace(' ', '', $r['status'])) ?>"><?= h($r['status']) ?></span></td>
            <td><?= h($r['updated_at'] ?: $r['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <h2 class="mt-4" style="color:#003d82">Message the barangay</h2>
  <form class="card-soft" method="post" style="max-width:560px">
    <input type="hidden" name="action" value="send_message" />
    <textarea class="form-control mb-2" name="message" rows="3" required></textarea>
    <button class="btn btn-warning fw-bold" type="submit">Send</button>
  </form>
  <?php foreach ($msgs as $m): ?>
    <p class="mt-2"><strong><?= h($m['sender_name']) ?>:</strong> <?= h($m['message']) ?></p>
  <?php endforeach; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= h(base_url('assets/govly2.js')) ?>"></script>
</body></html>
