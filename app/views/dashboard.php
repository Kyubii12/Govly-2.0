<?php
$u = require_login();
if (is_admin_user($u)) {
    redirect('admin');
}
$title = 'Resident Services — Barangay Sta. Cruz';
$active = is_active_resident($u);
$site = site();
$services = $site['services'] ?? [];
$serviceMap = [];
foreach ($services as $s) {
    $serviceMap[(string) ($s['type'] ?? '')] = $s;
}
$reqs = db_all('SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC', [$u['id']]);
$msgs = db_all(
    'SELECT * FROM messages WHERE sender_id = ? OR (sender_role = "admin" AND recipient_role = "resident") ORDER BY created_at DESC LIMIT 20',
    [$u['id']]
);
$pendingCount = count(array_filter($reqs, fn($r) => in_array($r['status'], ['Pending', 'Approved', 'In Progress'], true)));
$doneCount = count(array_filter($reqs, fn($r) => $r['status'] === 'Completed'));
$fullName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
require __DIR__ . '/_head.php';
require __DIR__ . '/_nav.php';
?>
<div class="resident-dash">
  <section class="resident-hero">
    <div class="container-xl px-3 py-4 py-md-5">
      <p class="resident-kicker mb-2">Barangay Sta. Cruz · Bacnotan, La Union</p>
      <h1 class="resident-title mb-2">Welcome, <?= h($u['first_name']) ?></h1>
      <p class="resident-lead mb-3">
        Request barangay certificates online. Updates are sent to
        <strong><?= h($u['email'] ?: 'your registered email') ?></strong>.
      </p>
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="resident-pill <?= $active ? 'is-active' : 'is-pending' ?>">
          <?= $active ? 'Account verified' : 'Awaiting barangay approval' ?>
        </span>
        <?php if ($u['purok'] ?? ''): ?>
          <span class="resident-pill is-muted"><?= h($u['purok']) ?></span>
        <?php endif; ?>
        <span class="resident-pill is-muted"><?= (int) $pendingCount ?> in process · <?= (int) $doneCount ?> completed</span>
      </div>
    </div>
  </section>

  <div class="container-xl px-3 pb-5" style="margin-top:-1.25rem">
    <?php if (!$active): ?>
      <div class="resident-card p-4 mb-4">
        <h2 class="h5 fw-bold mb-2" style="color:#0038A8">Registration under review</h2>
        <p class="mb-0 text-muted">
          Your account is being verified by the barangay office. Once approved, you can submit certificate requests here.
          Please keep this page bookmarked and watch your email for updates.
        </p>
      </div>
    <?php else: ?>
      <div class="row g-4 mb-4">
        <div class="col-lg-7">
          <div class="resident-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
              <div>
                <h2 class="h5 fw-bold mb-1" style="color:#0038A8">Request a certificate</h2>
                <p class="text-muted small mb-0">Fill out the form carefully. Incomplete purpose may delay processing.</p>
              </div>
            </div>
            <form method="post" id="certRequestForm">
              <input type="hidden" name="action" value="request_cert" />
              <div class="mb-3">
                <label class="form-label fw-semibold">Certificate type</label>
                <select class="form-select" name="certificate_type" id="certType" required>
                  <option value="">Select certificate type</option>
                  <?php foreach (cert_types() as $t):
                    $svc = $serviceMap[$t] ?? null;
                    $meta = $svc ? (($svc['price'] ?? '—') . ' · ' . ($svc['time'] ?? '1–2 days')) : '';
                  ?>
                    <option value="<?= h($t) ?>" data-price="<?= h($svc['price'] ?? '—') ?>" data-time="<?= h($svc['time'] ?? '1–2 days') ?>" data-desc="<?= h($svc['desc'] ?? '') ?>">
                      <?= h($t) ?><?= $meta !== '' ? ' (' . h($meta) . ')' : '' ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <p class="small mb-0 mt-2" id="certMeta" style="color:#9a3412;display:none"></p>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Purpose</label>
                <textarea class="form-control" name="purpose" rows="3" required
                  placeholder="Example: for employment, school enrollment, or medical assistance"></textarea>
                <div class="form-text">State the exact reason so the office can prepare the correct wording.</div>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Payment</label>
                <select class="form-select" name="payment_mode" required>
                  <option value="Pay at Barangay Hall" selected>Pay at Barangay Hall (cash)</option>
                </select>
                <div class="form-text">Fees are paid in cash upon release at the Barangay Hall.</div>
              </div>
              <button class="btn btn-primary-custom fw-bold px-4 py-2 rounded-3" type="submit">Submit request</button>
            </form>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="resident-card p-4 h-100">
            <h2 class="h5 fw-bold mb-3" style="color:#0038A8">How it works</h2>
            <ol class="resident-steps mb-4">
              <li><strong>Submit</strong> your certificate request online.</li>
              <li><strong>Wait</strong> while the barangay reviews and prepares your document.</li>
              <li><strong>Get notified</strong> by email when it is ready for pickup.</li>
              <li><strong>Claim</strong> at the Barangay Hall and pay the posted fee in cash.</li>
            </ol>
            <div class="resident-note">
              <strong>Office reminder</strong>
              <p class="mb-0 mt-1">
                Bring a valid ID when claiming. Processing follows the posted service time for each certificate.
                Status updates are sent to <strong><?= h($u['email'] ?: 'your email') ?></strong>.
              </p>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="resident-card p-4 mb-4">
      <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <div>
          <h2 class="h5 fw-bold mb-1" style="color:#0038A8">My requests</h2>
          <p class="text-muted small mb-0">Track every certificate you have filed with the barangay.</p>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table align-middle mb-0 resident-table">
          <thead>
            <tr>
              <th>Ref No.</th>
              <th>Certificate</th>
              <th>Purpose</th>
              <th>Status</th>
              <th>Updated</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$reqs): ?>
              <tr>
                <td colspan="5" class="text-center text-muted py-4">
                  No requests yet.<?= $active ? ' Use the form above to file your first certificate request.' : '' ?>
                </td>
              </tr>
            <?php else: foreach ($reqs as $r): ?>
              <tr>
                <td class="fw-semibold">#<?= (int) $r['id'] ?></td>
                <td><?= h($r['certificate_type']) ?></td>
                <td class="text-muted" style="max-width:280px"><?= h($r['purpose']) ?></td>
                <td>
                  <span class="badge rounded-pill px-2 py-1" style="<?= h(status_badge_style($r['status'])) ?>;font-size:.75rem">
                    <?= h($r['status']) ?>
                  </span>
                </td>
                <td class="text-muted small text-nowrap"><?= h($r['updated_at'] ?: $r['created_at']) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="resident-card p-4 h-100">
          <h2 class="h5 fw-bold mb-2" style="color:#0038A8">Message the barangay</h2>
          <p class="text-muted small mb-3">For simple inquiries only. Emergency concerns should use the hotlines on the Emergency page.</p>
          <form method="post">
            <input type="hidden" name="action" value="send_message" />
            <textarea class="form-control mb-3" name="message" rows="4" required
              placeholder="Write your message to the barangay office…"></textarea>
            <button class="btn btn-outline-primary fw-semibold rounded-3" type="submit">Send message</button>
          </form>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="resident-card p-4 h-100">
          <h2 class="h5 fw-bold mb-3" style="color:#0038A8">Recent messages</h2>
          <?php if (!$msgs): ?>
            <p class="text-muted mb-0">No messages yet.</p>
          <?php else: ?>
            <div class="resident-msg-list">
              <?php foreach ($msgs as $m): ?>
                <div class="resident-msg">
                  <div class="d-flex justify-content-between gap-2 flex-wrap">
                    <strong><?= h($m['sender_name']) ?></strong>
                    <span class="text-muted small"><?= h($m['created_at']) ?></span>
                  </div>
                  <p class="mb-0 mt-1"><?= h($m['message']) ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <p class="text-center text-muted small mt-4 mb-0">
      Signed in as <?= h($fullName !== '' ? $fullName : ($u['email'] ?? 'Resident')) ?> · Barangay Sta. Cruz Online Services
    </p>
  </div>
</div>

<style>
.resident-dash { background: #f3f6fb; min-height: 70vh; }
.resident-hero {
  background: linear-gradient(135deg, #0038A8 0%, #002570 55%, #001a47 100%);
  color: #fff;
  position: relative;
  overflow: hidden;
}
.resident-hero::after {
  content: "";
  position: absolute; inset: 0;
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
  pointer-events: none;
}
.resident-kicker {
  text-transform: uppercase;
  letter-spacing: .12em;
  font-size: .72rem;
  opacity: .85;
  position: relative;
  z-index: 1;
}
.resident-title {
  font-size: clamp(1.6rem, 3vw, 2.2rem);
  font-weight: 800;
  position: relative;
  z-index: 1;
}
.resident-lead {
  max-width: 42rem;
  opacity: .92;
  position: relative;
  z-index: 1;
}
.resident-pill {
  display: inline-flex;
  align-items: center;
  padding: .35rem .75rem;
  border-radius: 999px;
  font-size: .8rem;
  font-weight: 700;
  position: relative;
  z-index: 1;
}
.resident-pill.is-active { background: #dcfce7; color: #166534; }
.resident-pill.is-pending { background: #fef3c7; color: #92400e; }
.resident-pill.is-muted { background: rgba(255,255,255,.14); color: #fff; }
.resident-card {
  background: #fff;
  border: 1px solid #e4ebf3;
  border-radius: 1rem;
  box-shadow: 0 10px 30px rgba(15, 23, 42, .05);
}
.resident-steps {
  padding-left: 1.1rem;
  color: #334155;
  line-height: 1.55;
}
.resident-steps li { margin-bottom: .65rem; }
.resident-note {
  background: #fff7ed;
  border: 1px solid #fed7aa;
  border-radius: .85rem;
  padding: .9rem 1rem;
  color: #9a3412;
  font-size: .9rem;
}
.resident-table thead th {
  font-size: .72rem;
  text-transform: uppercase;
  letter-spacing: .04em;
  color: #64748b;
  background: #f8fafc;
  border-bottom-color: #e2e8f0;
}
.resident-msg-list { display: flex; flex-direction: column; gap: .75rem; max-height: 320px; overflow-y: auto; }
.resident-msg {
  border: 1px solid #e8eef5;
  border-radius: .75rem;
  padding: .75rem .9rem;
  background: #f8fafc;
}
@media (max-width: 767.98px) {
  .resident-hero .container-xl { padding-top: 1.25rem !important; padding-bottom: 2rem !important; }
}
</style>
<script>
(function () {
  var sel = document.getElementById('certType');
  var meta = document.getElementById('certMeta');
  if (!sel || !meta) return;
  function sync() {
    var opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) { meta.style.display = 'none'; meta.textContent = ''; return; }
    var price = opt.getAttribute('data-price') || '—';
    var time = opt.getAttribute('data-time') || '1–2 days';
    var desc = opt.getAttribute('data-desc') || '';
    meta.style.display = 'block';
    meta.textContent = 'Posted fee: ' + price + ' · Processing time: ' + time + (desc ? ' — ' + desc : '');
  }
  sel.addEventListener('change', sync);
  sync();
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= h(base_url('assets/govly2.js')) ?>?v=11"></script>
</body></html>
