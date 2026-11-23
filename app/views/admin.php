<?php
$u = require_admin();
$tab = $_GET['tab'] ?? 'dashboard';
$site = site();
$nav = [
  'dashboard' => 'Dashboard',
  'registrations' => 'Registrations',
  'registered' => 'Registered Residents',
  'census' => 'Census',
  'requests' => 'Requests',
  'activity' => 'Activity logs',
  'certificates' => 'Certificates',
  'announcements' => 'Announcements',
  'officials' => 'Officials',
  'website' => 'Prices & Contacts',
  'admins' => 'Admin Accounts',
  'messages' => 'Messages',
];
$title = 'Admin — GovLy 2.0';
require __DIR__ . '/_head.php';
require __DIR__ . '/_nav.php';
?>
<div class="admin-shell">
  <aside class="side">
    <div class="side-brand">
      <strong>GovLy 2.0 Admin</strong>
      <p class="muted"><?= h($u['first_name'] . ' ' . $u['last_name']) ?> · <?= h($u['role']) ?></p>
    </div>
    <?php foreach ($nav as $id => $label): ?>
      <a class="<?= $tab === $id ? 'active' : '' ?>" href="<?= h(base_url('admin?tab=' . $id)) ?>"><?= h($label) ?></a>
    <?php endforeach; ?>
    <a href="<?= h(base_url()) ?>">View website</a>
    <a href="<?= h(base_url('logout')) ?>">Logout</a>
  </aside>
  <main class="main">
    <?php if ($tab === 'dashboard'):
      $listed = (int) (db_one('SELECT COUNT(*) c FROM residents')['c'] ?? 0);
      $active = (int) (db_one("SELECT COUNT(*) c FROM residents WHERE status = 'Active' OR authorized = 1")['c'] ?? 0);
      $pending = (int) (db_one("SELECT COUNT(*) c FROM registrations WHERE status = 'Pending'")['c'] ?? 0);
      $open = (int) (db_one("SELECT COUNT(*) c FROM requests WHERE status IN ('Pending','Approved','In Progress')")['c'] ?? 0);
      $done = (int) (db_one("SELECT COUNT(*) c FROM requests WHERE status = 'Completed'")['c'] ?? 0);
      $ages = ['0-17' => 0, '18-59' => 0, '60+' => 0, 'Unknown' => 0];
      foreach (db_all('SELECT birth_date FROM residents') as $row) {
          $ages[age_group(age_from_birth($row['birth_date']))]++;
      }
    ?>
      <h2>Dashboard</h2>
      <p class="muted">Email OTP registration · Email when certificate is Completed · No SMS</p>
      <div class="grid cards-3">
        <div class="card stat"><h3>Census</h3><p><?= $listed ?></p></div>
        <div class="card stat"><h3>Active residents</h3><p><?= $active ?></p></div>
        <div class="card stat"><h3>Pending registrations</h3><p><?= $pending ?></p></div>
        <div class="card stat"><h3>Open requests</h3><p><?= $open ?></p></div>
        <div class="card stat"><h3>Completed</h3><p><?= $done ?></p></div>
      </div>
      <div class="card" style="margin-top:16px">
        <h3>Census age groups</h3>
        <?php foreach ($ages as $k => $v): ?><p><?= h($k) ?>: <strong><?= (int) $v ?></strong></p><?php endforeach; ?>
      </div>

    <?php elseif ($tab === 'registrations'):
      $rows = db_all('SELECT * FROM registrations ORDER BY FIELD(status,"Pending","Approved","Rejected"), created_at DESC');
    ?>
      <h2>Registrations</h2>
      <div class="table-wrap">
        <table class="data">
          <tr><th>Name</th><th>Email</th><th>Phone</th><th>Purok</th><th>Status</th><th></th></tr>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= h(trim($r['first_name'] . ' ' . $r['last_name'])) ?></td>
              <td><?= h($r['email'] ?? '') ?></td>
              <td><?= h($r['phone']) ?></td>
              <td><?= h($r['purok']) ?></td>
              <td><span class="badge <?= strtolower($r['status']) ?>"><?= h($r['status']) ?></span></td>
              <td>
                <?php if ($r['status'] === 'Pending'): ?>
                  <form method="post" style="display:inline"><input type="hidden" name="action" value="approve_reg" /><input type="hidden" name="id" value="<?= (int) $r['id'] ?>" /><button class="btn btn-navy">Approve</button></form>
                  <form method="post" style="display:inline"><input type="hidden" name="action" value="reject_reg" /><input type="hidden" name="id" value="<?= (int) $r['id'] ?>" /><button class="btn btn-outline">Reject</button></form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>

    <?php elseif ($tab === 'registered'):
      $rows = db_all("SELECT * FROM users WHERE role = 'resident' AND status = 'active' ORDER BY last_name");
    ?>
      <h2>Registered Residents</h2>
      <div class="table-wrap">
        <table class="data">
          <tr><th>Name</th><th>Email</th><th>Phone</th><th>Purok</th></tr>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= h(trim($r['first_name'] . ' ' . $r['last_name'])) ?></td>
              <td><?= h($r['email']) ?></td>
              <td><?= h($r['phone']) ?></td>
              <td><?= h($r['purok']) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>

    <?php elseif ($tab === 'census'):
      $q = trim((string) ($_GET['q'] ?? ''));
      $sql = 'SELECT * FROM residents';
      $p = [];
      if ($q !== '') { $sql .= ' WHERE full_name LIKE ? OR phone LIKE ?'; $p = ["%$q%", "%$q%"]; }
      $sql .= ' ORDER BY full_name';
      $rows = db_all($sql, $p);
      $edit = isset($_GET['edit']) ? db_one('SELECT * FROM residents WHERE id = ?', [(int) $_GET['edit']]) : null;
      $parts = $edit ? preg_split('/\s+/', trim($edit['full_name'])) : [];
    ?>
      <h2>Census</h2>
      <form class="inline-search" method="get" action="<?= h(base_url('admin')) ?>">
        <input type="hidden" name="tab" value="census" />
        <input name="q" value="<?= h($q) ?>" placeholder="Search name or phone" />
        <button class="btn btn-outline">Search</button>
      </form>
      <div class="card" style="margin:16px 0">
        <h3><?= $edit ? 'Edit resident' : 'Add census resident' ?></h3>
        <form class="form" method="post">
          <input type="hidden" name="action" value="save_resident" />
          <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>" />
          <div class="row">
            <input name="first_name" required placeholder="First name" value="<?= h($parts[0] ?? '') ?>" />
            <input name="middle_name" placeholder="Middle name" value="<?= h(count($parts) > 2 ? implode(' ', array_slice($parts, 1, -1)) : '') ?>" />
            <input name="last_name" required placeholder="Last name" value="<?= h(count($parts) > 1 ? $parts[count($parts) - 1] : '') ?>" />
          </div>
          <input name="address" placeholder="Address" value="<?= h($edit['address'] ?? '') ?>" />
          <select name="purok"><?php foreach (puroks() as $pk): ?><option <?= (($edit['purok'] ?? '') === $pk) ? 'selected' : '' ?>><?= h($pk) ?></option><?php endforeach; ?></select>
          <input name="phone" placeholder="Phone" value="<?= h($edit['phone'] ?? '') ?>" />
          <input name="birth_date" type="date" value="<?= h($edit['birth_date'] ?? '') ?>" />
          <input name="occupation" placeholder="Occupation" value="<?= h($edit['occupation'] ?? '') ?>" />
          <button class="btn btn-navy" type="submit">Save</button>
        </form>
      </div>
      <div class="table-wrap">
        <table class="data">
          <tr><th>Name</th><th>Purok</th><th>Phone</th><th>Age</th><th>Status</th><th></th></tr>
          <?php foreach ($rows as $r): $age = age_from_birth($r['birth_date']); ?>
            <tr>
              <td><?= h($r['full_name']) ?></td>
              <td><?= h($r['purok']) ?></td>
              <td><?= h($r['phone']) ?></td>
              <td><?= $age === null ? '—' : (int) $age ?></td>
              <td><span class="badge <?= $r['status'] === 'Active' || $r['authorized'] ? 'active' : 'listed' ?>"><?= $r['status'] === 'Active' || $r['authorized'] ? 'Active' : 'Listed' ?></span></td>
              <td class="actions-cell">
                <a class="btn btn-outline" href="<?= h(base_url('admin?tab=census&edit=' . $r['id'])) ?>">Edit</a>
                <form method="post" style="display:inline" onsubmit="return confirm('Create walk-in print?')">
                  <input type="hidden" name="action" value="walkin_print" />
                  <input type="hidden" name="resident_id" value="<?= (int) $r['id'] ?>" />
                  <select name="certificate_type"><?php foreach (cert_types() as $t): ?><option><?= h($t) ?></option><?php endforeach; ?></select>
                  <button class="btn btn-amber">Print</button>
                </form>
                <?php if ($r['status'] !== 'Active' && !$r['authorized']): ?>
                  <form method="post" class="form authorize-form">
                    <input type="hidden" name="action" value="authorize_resident" />
                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
                    <input type="hidden" name="mode" value="create" />
                    <input name="phone" placeholder="Mobile" value="<?= h($r['phone']) ?>" />
                    <input name="email" type="email" placeholder="Email (for notices)" />
                    <input name="password" type="password" placeholder="Temp password" />
                    <button class="btn btn-navy">Authorize</button>
                  </form>
                <?php endif; ?>
                <form method="post" style="display:inline" onsubmit="return confirm('Remove from census?')">
                  <input type="hidden" name="action" value="delete_resident" /><input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
                  <button class="btn btn-outline">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>

    <?php elseif ($tab === 'requests'):
      $filter = $_GET['filter'] ?? 'All';
      $rows = db_all('SELECT * FROM requests ORDER BY FIELD(status,"Pending","Approved","In Progress","Completed","Rejected"), created_at DESC');
      if ($filter !== 'All') {
          $rows = array_values(array_filter($rows, fn($r) => $r['status'] === $filter));
      }
    ?>
      <h2>Certificate requests</h2>
      <p class="muted">When you set status to <strong>Completed</strong> (or Rejected), the resident is emailed. No SMS.</p>
      <div class="filter-row">
        <?php foreach (['All','Pending','Approved','In Progress','Completed','Rejected'] as $f): ?>
          <a class="btn <?= $filter === $f ? 'btn-navy' : 'btn-outline' ?>" href="<?= h(base_url('admin?tab=requests&filter=' . urlencode($f))) ?>"><?= h($f) ?></a>
        <?php endforeach; ?>
      </div>
      <div class="table-wrap">
        <table class="data">
          <tr><th>#</th><th>Resident</th><th>Type</th><th>Purpose</th><th>Status</th><th>Update</th></tr>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= (int) $r['id'] ?></td>
              <td>
                <?= h($r['resident_name']) ?><br>
                <span class="muted"><?= h($r['email'] ?: $r['phone']) ?></span>
                <?= $r['walk_in'] ? '<br><span class="badge listed">Walk-in</span>' : '' ?>
              </td>
              <td><?= h($r['certificate_type']) ?></td>
              <td><?= h($r['purpose']) ?></td>
              <td><span class="badge badge-<?= h(str_replace(' ', '', $r['status'])) ?>"><?= h($r['status']) ?></span></td>
              <td>
                <a class="btn btn-amber" href="<?= h(base_url('print-cert?id=' . $r['id'])) ?>">Print</a>
                <form method="post" class="form" style="margin-top:6px;min-width:180px">
                  <input type="hidden" name="action" value="update_request" />
                  <input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
                  <select name="status">
                    <?php foreach (['Pending','Approved','In Progress','Completed','Rejected'] as $s): ?>
                      <option <?= $r['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                  </select>
                  <input name="admin_note" placeholder="Note (optional)" />
                  <button class="btn btn-navy">Save &amp; email</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>

    <?php elseif ($tab === 'activity'):
      $logs = db_all('SELECT * FROM activity_logs ORDER BY id DESC LIMIT 200');
    ?>
      <h2>Activity logs</h2>
      <div class="table-wrap">
        <table class="data">
          <tr><th>When</th><th>Action</th><th>Detail</th><th>By</th></tr>
          <?php foreach ($logs as $l): ?>
            <tr>
              <td><?= h($l['created_at']) ?></td>
              <td><?= h($l['action']) ?></td>
              <td><?= h($l['detail']) ?></td>
              <td><?= h($l['actor']) ?> <span class="muted">(<?= h($l['actor_role']) ?>)</span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$logs): ?><tr><td colspan="4" class="muted">No activity yet.</td></tr><?php endif; ?>
        </table>
      </div>

    <?php elseif ($tab === 'certificates'):
      $types = cert_types();
      $cur = $_GET['type'] ?? ($types[0] ?? '');
      $tpl = $cur ? db_one('SELECT * FROM cert_templates WHERE type = ?', [$cur]) : null;
    ?>
      <h2>Certificate templates</h2>
      <form method="post" class="form" style="max-width:420px;margin-bottom:16px">
        <input type="hidden" name="action" value="add_template" />
        <input name="type" placeholder="New certificate name" />
        <button class="btn btn-navy">Add</button>
      </form>
      <div class="filter-row">
        <?php foreach ($types as $t): ?>
          <a class="btn <?= $t === $cur ? 'btn-navy' : 'btn-outline' ?>" href="<?= h(base_url('admin?tab=certificates&type=' . urlencode($t))) ?>"><?= h($t) ?></a>
        <?php endforeach; ?>
      </div>
      <?php if ($tpl): ?>
        <form method="post" class="card form" style="margin-top:16px">
          <input type="hidden" name="action" value="save_template" />
          <input type="hidden" name="type" value="<?= h($cur) ?>" />
          <label>Body<textarea name="body" rows="5"><?= h($tpl['body']) ?></textarea></label>
          <label>Purpose<input name="purpose" value="<?= h($tpl['purpose']) ?>" /></label>
          <label>Details<textarea name="details" rows="3"><?= h($tpl['details']) ?></textarea></label>
          <label>Closing<textarea name="closing" rows="3"><?= h($tpl['closing']) ?></textarea></label>
          <button class="btn btn-amber">Save template</button>
        </form>
        <form method="post" onsubmit="return confirm('Delete this template?')">
          <input type="hidden" name="action" value="delete_template" /><input type="hidden" name="type" value="<?= h($cur) ?>" />
          <button class="btn btn-outline">Delete</button>
        </form>
      <?php endif; ?>

    <?php elseif ($tab === 'announcements'):
      $rows = db_all('SELECT * FROM announcements ORDER BY created_at DESC');
    ?>
      <h2>Announcements</h2>
      <form method="post" class="form card">
        <input type="hidden" name="action" value="save_announcement" />
        <input name="title" required placeholder="Title" />
        <textarea name="body" rows="4" placeholder="Body"></textarea>
        <button class="btn btn-navy">Post</button>
      </form>
      <?php foreach ($rows as $r): ?>
        <div class="card" style="margin-top:10px">
          <strong><?= h($r['title']) ?></strong>
          <p><?= nl2br(h($r['body'])) ?></p>
          <form method="post"><input type="hidden" name="action" value="delete_announcement" /><input type="hidden" name="id" value="<?= (int) $r['id'] ?>" /><button class="btn btn-outline">Delete</button></form>
        </div>
      <?php endforeach; ?>

    <?php elseif ($tab === 'officials'):
      $rows = db_all('SELECT * FROM officials ORDER BY order_num, name');
    ?>
      <h2>Officials</h2>
      <p class="muted">Add only real officials with a photo.</p>
      <form method="post" enctype="multipart/form-data" class="form card">
        <input type="hidden" name="action" value="save_official" />
        <input name="name" required placeholder="Full name" />
        <select name="position"><?php foreach (positions() as $p): ?><option><?= h($p) ?></option><?php endforeach; ?></select>
        <input name="order_num" type="number" min="1" max="7" placeholder="Kagawad order 1-7" />
        <textarea name="description" placeholder="Description"></textarea>
        <input type="file" name="photo" accept="image/*" required />
        <button class="btn btn-navy">Add official</button>
      </form>
      <div class="official-row" style="margin-top:16px">
        <?php foreach ($rows as $o): ?>
          <div class="official-card">
            <?php if ($o['photo_url']): ?><img src="<?= h(base_url($o['photo_url'])) ?>" alt="" /><?php endif; ?>
            <strong><?= h($o['name']) ?></strong>
            <span class="muted"><?= h($o['position']) ?></span>
            <form method="post"><input type="hidden" name="action" value="delete_official" /><input type="hidden" name="id" value="<?= (int) $o['id'] ?>" /><button class="btn btn-outline">Remove</button></form>
          </div>
        <?php endforeach; ?>
      </div>

    <?php elseif ($tab === 'website'): ?>
      <h2>Prices &amp; Contacts</h2>
      <form method="post" class="form card">
        <input type="hidden" name="action" value="save_site" />
        <label>Emergency call<input name="emergencyCall" value="<?= h($site['emergencyCall'] ?? '') ?>" /></label>
        <label>Emergency lead<textarea name="emergencyLead" rows="3"><?= h($site['emergencyLead'] ?? '') ?></textarea></label>
        <label>Office address<textarea name="officeAddress" rows="2"><?= h($site['officeAddress'] ?? '') ?></textarea></label>
        <label>Contact phones<textarea name="contactPhones" rows="2"><?= h($site['contactPhones'] ?? '') ?></textarea></label>
        <label>Contact email<input name="contactEmail" value="<?= h($site['contactEmail'] ?? '') ?>" /></label>
        <label>Office hours<textarea name="officeHours" rows="2"><?= h($site['officeHours'] ?? '') ?></textarea></label>
        <label>Footer location<input name="footerLocation" value="<?= h($site['footerLocation'] ?? '') ?>" /></label>
        <h3>Service prices</h3>
        <?php foreach ($site['services'] ?? [] as $i => $s): ?>
          <div class="service-edit">
            <strong><?= h($s['type']) ?></strong>
            <input name="price[<?= $i ?>]" value="<?= h($s['price'] ?? '') ?>" placeholder="Price" />
            <input name="time[<?= $i ?>]" value="<?= h($s['time'] ?? '') ?>" placeholder="Time" />
            <input name="desc[<?= $i ?>]" value="<?= h($s['desc'] ?? '') ?>" placeholder="Description" />
          </div>
        <?php endforeach; ?>
        <button class="btn btn-navy">Save</button>
      </form>

    <?php elseif ($tab === 'admins'):
      $rows = db_all("SELECT * FROM users WHERE role IN ('admin','super_admin') ORDER BY role DESC, created_at");
    ?>
      <h2>Admin accounts</h2>
      <?php if (($u['role'] ?? '') === 'super_admin'): ?>
        <form method="post" class="form card">
          <input type="hidden" name="action" value="save_admin" />
          <input name="first_name" required placeholder="First name" />
          <input name="last_name" required placeholder="Last name" />
          <input name="phone" required placeholder="Mobile" />
          <input name="email" placeholder="Email" />
          <input name="password" type="password" required minlength="8" placeholder="Password" />
          <button class="btn btn-navy">Create admin (max 3)</button>
        </form>
      <?php else: ?>
        <p class="muted">Only Super Admin can create accounts.</p>
      <?php endif; ?>
      <table class="data">
        <tr><th>Name</th><th>Role</th><th>Phone</th><th>Email</th><th></th></tr>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= h($r['first_name'] . ' ' . $r['last_name']) ?></td>
            <td><?= h($r['role']) ?></td>
            <td><?= h($r['phone']) ?></td>
            <td><?= h($r['email']) ?></td>
            <td>
              <?php if (($u['role'] ?? '') === 'super_admin' && $r['role'] === 'admin'): ?>
                <form method="post" onsubmit="return confirm('Delete this admin?')">
                  <input type="hidden" name="action" value="delete_admin" />
                  <input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
                  <button class="btn btn-outline">Delete</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>

    <?php elseif ($tab === 'messages'):
      $rows = db_all('SELECT * FROM messages ORDER BY created_at DESC LIMIT 80');
    ?>
      <h2>Messages</h2>
      <form method="post" class="form card">
        <input type="hidden" name="action" value="reply_message" />
        <textarea name="message" required placeholder="Reply to residents"></textarea>
        <button class="btn btn-navy">Reply</button>
      </form>
      <?php foreach ($rows as $m): ?>
        <div class="card" style="margin-top:8px">
          <strong><?= h($m['sender_name']) ?> (<?= h($m['sender_role']) ?>)</strong>
          <p><?= h($m['message']) ?></p>
          <p class="muted"><?= h($m['created_at']) ?></p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= h(base_url('assets/govly2.js')) ?>"></script>
</body></html>
