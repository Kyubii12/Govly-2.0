<?php
$u = require_admin();
$tab = $_GET['tab'] ?? 'dashboard';
if ($tab === 'census') {
    $tab = 'residents';
}
if ($tab === 'settings') {
    $tab = 'admins';
}
$site = site();
$isSuper = is_super_admin($u);
$roleClass = $isSuper ? 'role-super' : 'role-admin';
$roleText = role_label($u);
$accent = $isSuper ? '#fbbf24' : '#5eead4';
$accentBg = $isSuper ? '#fbbf24' : '#5eead4';
$accentFg = $isSuper ? '#0f172a' : '#042f2e';
$avatarBg = $isSuper ? '#d97706' : '#0d9488';
$topbarClass = $isSuper ? 'topbar-super' : 'topbar-admin';
$badgeBg = $isSuper ? '#1e3a5f' : '#0f766e';

$nav = [
  'dashboard' => 'Dashboard',
  'registrations' => 'Registrations',
  'registered' => 'Registered Residents',
  'residents' => 'Census',
  'requests' => 'Requests',
  'activity' => 'Activity logs',
  'certificates' => 'Certificates',
  'announcements' => 'Announcements',
  'officials' => 'Officials',
  'website' => 'Prices & Contacts',
  'admins' => 'Settings',
  'messages' => 'Messages',
];
if (!isset($nav[$tab])) {
    $tab = 'dashboard';
}

$adminLayout = true;
$title = 'Admin — Barangay Sta.Cruz';
require __DIR__ . '/_head.php';
$initial = strtoupper(substr((string) ($u['first_name'] ?? 'A'), 0, 1));
?>
<?php
$adminName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?: 'Admin';
$todayLabel = date('F j, Y') . ' | ' . date('l');
$msgUnread = 0;
try {
    $msgUnread = (int) (db_one('SELECT COUNT(*) c FROM messages')['c'] ?? 0);
} catch (Throwable $e) {
    $msgUnread = 0;
}
$pageTitles = [
  'dashboard' => 'Dashboard Overview',
  'registrations' => 'Registrations Management',
  'registered' => 'Registered Residents',
  'residents' => 'Census Management',
  'requests' => 'Requests Management',
  'activity' => 'Activity Logs',
  'certificates' => 'Certificates Management',
  'announcements' => 'Announcements Management',
  'officials' => 'Officials Management',
  'website' => 'Prices & Contacts',
  'admins' => 'System Settings',
  'messages' => 'Messages',
];
$pageSubs = [
  'dashboard' => 'Welcome back — monitor census, registrations, and certificate requests.',
  'registrations' => 'Review and approve new resident registration applications.',
  'registered' => 'Manage and monitor barangay resident portal accounts.',
  'residents' => 'Manage barangay census records and population surveys.',
  'requests' => 'Process certificate requests, print documents, and send notices.',
  'activity' => 'Track admin actions across the barangay system.',
  'certificates' => 'Edit certificate templates used for official printing.',
  'announcements' => 'Create, schedule, and publish barangay announcements.',
  'officials' => 'Maintain barangay officials and positions.',
  'website' => 'Update public service prices and contact details.',
  'admins' => 'Manage account preferences, system configuration, and admin access.',
  'messages' => 'Read and respond to messages from residents.',
];
?>
<div class="admin-shell <?= h($roleClass) ?>">
  <button type="button" class="sidebar-backdrop d-lg-none" id="sidebarBackdrop" aria-label="Close menu" hidden></button>

  <aside id="sidebar" class="<?= h($roleClass) ?>">
    <div class="sidebar-brand">
      <div class="d-flex align-items-center gap-2">
        <img src="<?= h(base_url('assets/sta-cruz.jpg')) ?>" alt="Barangay Sta.Cruz Logo"
          class="rounded-circle flex-shrink-0"
          style="width:44px;height:44px;object-fit:cover;border:2px solid #fff;box-shadow:0 4px 12px rgba(37,99,235,.2)" />
        <div>
          <div class="sidebar-brand-title">Barangay Sta. Cruz</div>
          <div class="sidebar-brand-sub">Population Management</div>
          <div class="sidebar-brand-sub" style="margin-top:.15rem">Tamang Datos, Maayos na Barangay · Bacnotan, La Union</div>
        </div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <?php foreach ($nav as $id => $label): ?>
        <a href="<?= h(base_url('admin?tab=' . $id)) ?>"
          class="sidebar-link <?= $tab === $id ? 'active' : '' ?>">
          <?= admin_icon($id === 'residents' ? 'census' : ($id === 'admins' ? 'settings' : $id)) ?> <?= h($label) ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="sidebar-foot">
      <a href="<?= h(base_url()) ?>" class="sidebar-link">
        <?= admin_icon('globe', 16) ?> View website
      </a>
      <a href="<?= h(base_url('logout')) ?>" class="sidebar-link" style="color:#dc2626 !important">
        <?= admin_icon('logout', 16) ?> Logout
      </a>
    </div>
  </aside>

  <div class="admin-content">
    <div class="admin-topbar px-3 px-md-4 py-3 d-flex align-items-center justify-content-between sticky-top <?= h($topbarClass) ?>">
      <div class="d-flex align-items-center gap-3 flex-grow-1 me-3">
        <button type="button" class="btn d-lg-none p-1" id="sidebarToggle" aria-label="Open menu">
          <?= admin_icon('menu', 22) ?>
        </button>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="admin-icon-btn" id="adminThemeToggle" title="Night mode">
          <?= admin_icon('moon', 16) ?>
        </button>
        <a class="admin-icon-btn" href="<?= h(base_url('admin?tab=messages')) ?>" title="Messages">
          <?= admin_icon('bell', 16) ?>
          <?php if ($msgUnread > 0): ?><span class="dot"></span><?php endif; ?>
        </a>
        <div class="admin-profile-chip">
          <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
            style="width:34px;height:34px;font-size:.85rem;background:#2563eb">
            <?= h($initial) ?>
          </div>
          <div class="pe-1 d-none d-sm-block">
            <div class="fw-semibold" style="font-size:.8rem;line-height:1.1;color:#0f172a"><?= h($adminName) ?></div>
            <div style="font-size:.68rem;color:#64748b"><?= h($roleText) ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="p-4">
      <div class="admin-page-head">
        <div>
          <h1><?= h($pageTitles[$tab] ?? $nav[$tab]) ?></h1>
          <p class="admin-page-sub"><?= h($pageSubs[$tab] ?? '') ?></p>
          <p class="admin-page-date"><?= h($todayLabel) ?></p>
        </div>
        <div class="admin-head-actions">
          <?php if ($tab === 'residents'): ?>
            <a class="btn-admin-primary" href="<?= h(base_url('admin?tab=residents&edit=0#censusForm')) ?>"><?= admin_icon('plus', 14) ?> New Census</a>
            <a class="btn-admin-secondary" href="<?= h(base_url('admin?tab=registered')) ?>"><?= admin_icon('download', 14) ?> Export</a>
          <?php elseif ($tab === 'requests'): ?>
            <a class="btn-admin-primary" href="<?= h(base_url('admin?tab=requests&filter=Pending')) ?>">Pending Queue</a>
            <a class="btn-admin-secondary" href="<?= h(base_url('admin?tab=activity')) ?>"><?= admin_icon('download', 14) ?> Export</a>
          <?php elseif ($tab === 'registrations'): ?>
            <a class="btn-admin-primary" href="<?= h(base_url('admin?tab=registrations')) ?>">Review All</a>
            <a class="btn-admin-secondary" href="<?= h(base_url('admin?tab=registered')) ?>"><?= admin_icon('download', 14) ?> Export</a>
          <?php elseif ($tab === 'announcements'): ?>
            <a class="btn-admin-primary" href="#announcementForm"><?= admin_icon('plus', 14) ?> New Announcement</a>
            <a class="btn-admin-secondary" href="<?= h(base_url('admin?tab=activity')) ?>"><?= admin_icon('download', 14) ?> Export</a>
          <?php elseif ($tab === 'officials'): ?>
            <a class="btn-admin-primary" href="#officialForm"><?= admin_icon('plus', 14) ?> Add Official</a>
          <?php elseif ($tab === 'admins'): ?>
            <button form="settingsGeneralForm" class="btn-admin-primary" type="submit">Save Changes</button>
            <a class="btn-admin-secondary" href="<?= h(base_url('admin?tab=admins')) ?>">Reset</a>
            <?php if ($isSuper): ?>
              <a class="btn-admin-secondary" href="#adminForm"><?= admin_icon('plus', 14) ?> Add Admin</a>
            <?php endif; ?>
          <?php elseif ($tab === 'dashboard'): ?>
            <a class="btn-admin-primary" href="<?= h(base_url('admin?tab=requests')) ?>">Manage Requests</a>
            <a class="btn-admin-secondary" href="<?= h(base_url('admin?tab=residents')) ?>">Open Census</a>
          <?php elseif ($tab === 'registered'): ?>
            <a class="btn-admin-primary" href="<?= h(base_url('admin?tab=registrations')) ?>">Review Registrations</a>
            <a class="btn-admin-secondary" href="<?= h(base_url('admin?tab=residents')) ?>"><?= admin_icon('download', 14) ?> Export</a>
          <?php else: ?>
            <a class="btn-admin-secondary" href="<?= h(base_url()) ?>"><?= admin_icon('globe', 14) ?> Website</a>
          <?php endif; ?>
        </div>
      </div>
<?php if ($tab === 'dashboard'):
  $censusPeople = db_all('SELECT full_name, birth_date, purok, address, phone, status, authorized, created_at FROM residents ORDER BY id DESC');
  $censusAll = array_map(static fn($r) => ['birth_date' => $r['birth_date'] ?? null], $censusPeople);
  $censusCount = count($censusPeople);
  $registeredCount = (int) (db_one("SELECT COUNT(*) c FROM users WHERE role = 'resident' AND status = 'active'")['c'] ?? 0);
  $pendingReq = (int) (db_one("SELECT COUNT(*) c FROM requests WHERE status = 'Pending'")['c'] ?? 0);
  $inprogReq = (int) (db_one("SELECT COUNT(*) c FROM requests WHERE status = 'In Progress'")['c'] ?? 0);
  $doneReq = (int) (db_one("SELECT COUNT(*) c FROM requests WHERE status = 'Completed'")['c'] ?? 0);
  $rejectedReq = (int) (db_one("SELECT COUNT(*) c FROM requests WHERE status = 'Rejected'")['c'] ?? 0);
  $pendingReg = (int) (db_one("SELECT COUNT(*) c FROM registrations WHERE status = 'Pending'")['c'] ?? 0);
  $youth = $adult = $senior = 0;
  foreach ($censusPeople as $person) {
      $age = age_from_birth($person['birth_date'] ?? null);
      if ($age === null) {
          continue;
      }
      if ($age <= 17) {
          $youth++;
      } elseif ($age >= 60) {
          $senior++;
      } else {
          $adult++;
      }
  }
  $ageKnown = max(1, $youth + $adult + $senior);
  $purokRows = db_all("SELECT COALESCE(NULLIF(TRIM(purok), ''), 'Unspecified') AS purok, COUNT(*) AS c FROM residents GROUP BY COALESCE(NULLIF(TRIM(purok), ''), 'Unspecified') ORDER BY c DESC LIMIT 6");
  $purokMax = 1;
  foreach ($purokRows as $pr) {
      $purokMax = max($purokMax, (int) $pr['c']);
  }
  $recentActivity = db_all('SELECT * FROM activity_logs ORDER BY id DESC LIMIT 5');
  $recentResidents = array_slice($censusPeople, 0, 6);
  $recentRequests = db_all('SELECT id, resident_name, certificate_type, status, created_at FROM requests ORDER BY id DESC LIMIT 6');
  $growthYears = [];
  for ($y = (int) date('Y') - 5; $y <= (int) date('Y'); $y++) {
      $growthYears[$y] = (int) (db_one('SELECT COUNT(*) c FROM residents WHERE YEAR(created_at) <= ? OR created_at IS NULL', [$y])['c'] ?? 0);
  }
  if (max($growthYears) <= 0) {
      $base = max(1, $censusCount);
      $i = 0;
      foreach ($growthYears as $y => $_) {
          $growthYears[$y] = (int) round($base * (0.55 + ($i * 0.09)));
          $i++;
      }
  }
  $growthMax = max(1, max($growthYears));
  $growthPoints = [];
  $gi = 0;
  $gCount = max(1, count($growthYears) - 1);
  foreach ($growthYears as $y => $c) {
      $x = 20 + ($gi / $gCount) * 340;
      $yy = 120 - (($c / $growthMax) * 90);
      $growthPoints[] = round($x, 1) . ',' . round($yy, 1);
      $gi++;
  }
  $donutYouth = round(($youth / $ageKnown) * 100, 1);
  $donutAdult = round(($adult / $ageKnown) * 100, 1);
  $donutSenior = round(100 - $donutYouth - $donutAdult, 1);
  $regPct = number_format(($registeredCount / max(1, $censusCount)) * 100, 1) . '% of census';
  $youthPct = number_format(($youth / $ageKnown) * 100, 1) . '% of aged';
  $seniorPct = number_format(($senior / $ageKnown) * 100, 1) . '% of aged';
  $stats = [
    ['label' => 'Total Population', 'value' => $censusCount, 'color' => '#2563eb', 'bg' => '#eff6ff', 'icon' => 'census', 'href' => 'residents', 'trend' => 'Census headcount'],
    ['label' => 'Registered', 'value' => $registeredCount, 'color' => '#0891b2', 'bg' => '#ecfeff', 'icon' => 'registered', 'href' => 'registered', 'trend' => $regPct],
    ['label' => 'Youth (0-17)', 'value' => $youth, 'color' => '#7c3aed', 'bg' => '#f5f3ff', 'icon' => 'registrations', 'href' => 'residents&age=minor', 'trend' => $youthPct],
    ['label' => 'Senior Citizens', 'value' => $senior, 'color' => '#d97706', 'bg' => '#fffbeb', 'icon' => 'officials', 'href' => 'residents&age=senior', 'trend' => $seniorPct],
    ['label' => 'Pending Requests', 'value' => $pendingReq, 'color' => '#ea580c', 'bg' => '#fff7ed', 'icon' => 'requests', 'href' => 'requests&filter=Pending', 'trend' => 'Needs action'],
    ['label' => 'Completed', 'value' => $doneReq, 'color' => '#16a34a', 'bg' => '#f0fdf4', 'icon' => 'certificates', 'href' => 'requests&filter=Completed', 'trend' => 'Printed / released'],
  ];
?>
      <div class="welcome-banner d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
          <h2>Welcome back, <?= h($adminName) ?>!</h2>
          <p>Monitor population, registrations, and certificate requests for Barangay Sta. Cruz.</p>
        </div>
        <div class="admin-page-date mb-0"><?= h($todayLabel) ?></div>
      </div>

      <div class="row g-3 mb-4">
        <?php foreach ($stats as $s): ?>
          <div class="col-6 col-md-4 col-xl-2">
            <a href="<?= h(base_url('admin?tab=' . $s['href'])) ?>" class="text-decoration-none">
              <div class="admin-stat-card">
                <div class="d-flex justify-content-between align-items-start gap-2">
                  <div class="stat-label"><?= h($s['label']) ?></div>
                  <div class="admin-stat-icon" style="background:<?= h($s['bg']) ?>;color:<?= h($s['color']) ?>">
                    <?= admin_icon($s['icon'], 18) ?>
                  </div>
                </div>
                <div class="stat-value" style="color:<?= h($s['color']) ?>"><?= number_format((int) $s['value']) ?></div>
                <div class="stat-trend"><?= h($s['trend']) ?></div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-lg-7">
          <div class="admin-panel h-100">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">Population growth</h2>
                <p class="admin-panel-sub">Census records over recent years</p>
              </div>
            </div>
            <div class="p-3">
              <svg viewBox="0 0 380 150" width="100%" height="180" role="img" aria-label="Population growth chart">
                <defs>
                  <linearGradient id="popFill" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.35"/>
                    <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.02"/>
                  </linearGradient>
                </defs>
                <?php for ($g = 0; $g < 4; $g++): $gy = 30 + $g * 30; ?>
                  <line x1="20" y1="<?= $gy ?>" x2="360" y2="<?= $gy ?>" stroke="#e2e8f0" stroke-width="1"/>
                <?php endfor; ?>
                <polygon points="20,120 <?= implode(' ', $growthPoints) ?> 360,120" fill="url(#popFill)"/>
                <polyline points="<?= implode(' ', $growthPoints) ?>" fill="none" stroke="#2563eb" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <?php
                  $gi = 0;
                  foreach ($growthYears as $y => $c):
                    $x = 20 + ($gi / $gCount) * 340;
                    $yy = 120 - (($c / $growthMax) * 90);
                ?>
                  <circle cx="<?= round($x, 1) ?>" cy="<?= round($yy, 1) ?>" r="4" fill="#fff" stroke="#2563eb" stroke-width="2"/>
                  <text x="<?= round($x, 1) ?>" y="142" text-anchor="middle" font-size="11" fill="#64748b"><?= (int) $y ?></text>
                <?php $gi++; endforeach; ?>
              </svg>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="admin-panel h-100">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">Population by age group</h2>
                <p class="admin-panel-sub">Youth · Adults · Seniors</p>
              </div>
            </div>
            <div class="p-3 d-flex flex-wrap align-items-center gap-4 justify-content-center">
              <div class="admin-donut" style="--p1:<?= h((string) $donutYouth) ?>;--p2:<?= h((string) ($donutYouth + $donutAdult)) ?>">
                <div class="admin-donut-center">
                  <strong><?= number_format($censusCount) ?></strong>
                  <span>Total</span>
                </div>
              </div>
              <div class="admin-legend">
                <div><i style="background:#2563eb"></i> Youth 0–17 <b><?= (int) $youth ?> (<?= h((string) $donutYouth) ?>%)</b></div>
                <div><i style="background:#22c55e"></i> Adults 18–59 <b><?= (int) $adult ?> (<?= h((string) $donutAdult) ?>%)</b></div>
                <div><i style="background:#f59e0b"></i> Seniors 60+ <b><?= (int) $senior ?> (<?= h((string) $donutSenior) ?>%)</b></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-xl-8">
          <div class="admin-panel">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">Recent requests</h2>
                <p class="admin-panel-sub">Latest certificate requests from residents</p>
              </div>
              <a href="<?= h(base_url('admin?tab=requests')) ?>" class="btn-admin-secondary" style="padding:.4rem .8rem !important;font-size:.75rem !important">View all →</a>
            </div>
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead>
                  <tr>
                    <th>Ref</th><th>Resident</th><th>Certificate</th><th>Date</th><th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$recentRequests): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No requests yet.</td></tr>
                  <?php else: foreach ($recentRequests as $rr): ?>
                    <tr>
                      <td><a href="<?= h(base_url('admin?tab=requests&q=' . urlencode((string) $rr['id']))) ?>" class="fw-bold text-decoration-none" style="color:#2563eb">#<?= (int) $rr['id'] ?></a></td>
                      <td class="fw-semibold"><?= h($rr['resident_name']) ?></td>
                      <td><?= h($rr['certificate_type']) ?></td>
                      <td class="text-muted" style="font-size:.82rem"><?= $rr['created_at'] ? h(date('M j, Y', strtotime($rr['created_at']))) : '—' ?></td>
                      <td><span class="badge rounded-pill px-2 py-1" style="<?= h(status_badge_style($rr['status'] ?? 'Pending')) ?>;font-size:.72rem"><?= h($rr['status'] ?? 'Pending') ?></span></td>
                    </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="col-xl-4">
          <div class="admin-panel mb-3">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">Census by purok</h2>
                <p class="admin-panel-sub">Top puroks</p>
              </div>
            </div>
            <div class="p-3">
              <?php if (!$purokRows): ?>
                <p class="text-muted small mb-0">No purok data yet.</p>
              <?php else: ?>
                <div class="admin-mini-bar">
                  <?php foreach ($purokRows as $pr):
                    $w = ((int) $pr['c'] / $purokMax) * 100;
                  ?>
                    <div class="admin-mini-bar-row">
                      <span><?= h($pr['purok']) ?></span>
                      <div class="admin-mini-bar-track"><div class="admin-mini-bar-fill" style="width:<?= h((string) $w) ?>%"></div></div>
                      <em><?= (int) $pr['c'] ?></em>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
          <div class="admin-panel">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">Recent activity</h2>
                <p class="admin-panel-sub">Latest admin actions</p>
              </div>
              <a href="<?= h(base_url('admin?tab=activity')) ?>" style="font-size:.75rem;font-weight:700;color:#2563eb;text-decoration:none">View all</a>
            </div>
            <div class="px-3 pb-2">
              <?php if (!$recentActivity): ?>
                <p class="text-muted small py-3 mb-0">No activity yet.</p>
              <?php else: foreach ($recentActivity as $act): ?>
                <div class="admin-activity-item">
                  <div class="admin-activity-dot"></div>
                  <div>
                    <div class="fw-semibold" style="font-size:.84rem"><?= h($act['action'] ?? 'Action') ?></div>
                    <div class="text-muted" style="font-size:.72rem"><?= h($act['detail'] ?? '') ?></div>
                    <div class="text-muted" style="font-size:.68rem"><?= h($act['created_at'] ?? '') ?></div>
                  </div>
                </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>
      </div>

<?php elseif ($tab === 'registrations'):
  $rows = db_all('SELECT * FROM registrations ORDER BY FIELD(status,"Pending","Approved","Rejected"), created_at DESC');
?>
      <div class="bg-white rounded-4 shadow-sm border overflow-x-auto">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
          <span class="fw-semibold">Pending Registrations</span>
          <a href="<?= h(base_url('admin?tab=registrations')) ?>" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
            <?= admin_icon('refresh', 13) ?> Refresh
          </a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <?php foreach (['Name', 'Email', 'Phone', 'Purok', 'Age', 'Status', 'Actions'] as $h): ?>
                  <th style="font-size:.75rem;text-transform:uppercase;white-space:nowrap"><?= h($h) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php if (!$rows): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No registrations found.</td></tr>
              <?php else: foreach ($rows as $r):
                $age = age_from_birth($r['birth_date'] ?? null);
                $grp = age_group($age);
              ?>
                <tr>
                  <td class="fw-semibold" style="white-space:nowrap"><?= h(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))) ?></td>
                  <td class="text-muted" style="font-size:.875rem"><?= h($r['email'] ?? '—') ?></td>
                  <td class="text-muted" style="font-size:.875rem"><?= h($r['phone'] ?: '—') ?></td>
                  <td class="text-muted" style="font-size:.875rem"><?= h($r['purok'] ?: '—') ?></td>
                  <td class="text-muted" style="font-size:.875rem"><?= $age === null ? '—' : ((int) $age . ' · ' . age_group_label($grp)) ?></td>
                  <td>
                    <span class="badge rounded-pill px-2 py-1" style="<?= h(status_badge_style($r['status'] ?? 'Pending')) ?>;font-size:.75rem">
                      <?= h($r['status'] ?? 'Pending') ?>
                    </span>
                  </td>
                  <td>
                    <?php if (($r['status'] ?? '') === 'Pending'): ?>
                      <div class="d-flex flex-wrap gap-1">
                        <form method="post" class="d-inline">
                          <input type="hidden" name="action" value="approve_reg" />
                          <input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
                          <button class="btn btn-sm fw-semibold" style="background:#dcfce7;color:#166534;font-size:.75rem" type="submit">Approve</button>
                        </form>
                        <form method="post" class="d-inline">
                          <input type="hidden" name="action" value="reject_reg" />
                          <input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
                          <button class="btn btn-sm fw-semibold" style="background:#fee2e2;color:#991b1b;font-size:.75rem" type="submit">Reject</button>
                        </form>
                      </div>
                    <?php else: ?>
                      <span class="text-muted" style="font-size:.8rem">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

<?php elseif ($tab === 'registered'):
  $q = trim((string) ($_GET['q'] ?? ''));
  $rows = db_all("SELECT * FROM users WHERE role = 'resident' AND status = 'active' ORDER BY last_name, first_name");
  if ($q !== '') {
      $ql = strtolower($q);
      $rows = array_values(array_filter($rows, function ($r) use ($ql) {
          $hay = strtolower(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '') . ' ' . ($r['phone'] ?? '') . ' ' . ($r['purok'] ?? '') . ' ' . ($r['email'] ?? '')));
          return str_contains($hay, $ql);
      }));
  }
?>
      <div class="bg-white rounded-4 shadow-sm border overflow-x-auto">
        <div class="p-3 border-bottom">
          <form method="get" action="<?= h(base_url('admin')) ?>" class="d-flex gap-2 flex-wrap">
            <input type="hidden" name="tab" value="registered" />
            <input type="text" class="form-control" name="q" value="<?= h($q) ?>" placeholder="Search registered name, phone, or purok…" style="max-width:320px" />
            <button class="btn btn-outline-secondary btn-sm">Search</button>
          </form>
          <p class="text-muted small mb-0 mt-2">
            <?= count($rows) ?> registered resident<?= count($rows) === 1 ? '' : 's' ?> who created an account on the website
            <?= $q !== '' ? ' matching “' . h($q) . '”' : '' ?>
          </p>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <?php foreach (['Name', 'Email', 'Phone', 'Purok', 'Status'] as $h): ?>
                  <th style="font-size:.75rem;text-transform:uppercase;white-space:nowrap"><?= h($h) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php if (!$rows): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No website registrations have been approved yet.</td></tr>
              <?php else: foreach ($rows as $r): ?>
                <tr>
                  <td class="fw-semibold"><?= h(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))) ?></td>
                  <td class="text-muted" style="font-size:.875rem"><?= h($r['email'] ?: '—') ?></td>
                  <td class="text-muted" style="font-size:.875rem"><?= h($r['phone'] ?: '—') ?></td>
                  <td class="text-muted" style="font-size:.875rem"><?= h($r['purok'] ?: '—') ?></td>
                  <td><span class="badge rounded-pill px-2 py-1" style="background:#dcfce7;color:#166534;font-size:.75rem">Active</span></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

<?php elseif ($tab === 'residents'):
  $q = trim((string) ($_GET['q'] ?? ''));
  $purokFilter = trim((string) ($_GET['purok'] ?? ''));
  $ageFilter = trim((string) ($_GET['age'] ?? ''));
  $statusFilter = trim((string) ($_GET['status'] ?? ''));
  $page = max(1, (int) ($_GET['page'] ?? 1));
  $perPage = 10;
  $allCensus = db_all('SELECT * FROM residents ORDER BY id DESC');
  $totalCensus = count($allCensus);
  $activeCensus = 0;
  $pendingCensus = 0;
  $monthCensus = 0;
  $monthStart = date('Y-m-01 00:00:00');
  $purokCounts = [];
  foreach ($allCensus as $cr) {
      $isActive = (($cr['status'] ?? '') === 'Active') || !empty($cr['authorized']);
      if ($isActive) {
          $activeCensus++;
      } else {
          $pendingCensus++;
      }
      if (!empty($cr['created_at']) && $cr['created_at'] >= $monthStart) {
          $monthCensus++;
      }
      $pk = trim((string) ($cr['purok'] ?? '')) ?: 'Unspecified';
      $purokCounts[$pk] = ($purokCounts[$pk] ?? 0) + 1;
  }
  arsort($purokCounts);
  $purokCovered = count(array_filter(array_keys($purokCounts), static fn($k) => $k !== 'Unspecified'));
  $purokMax = max(1, $purokCounts ? max($purokCounts) : 1);
  $rows = $allCensus;
  if ($q !== '') {
      $ql = strtolower($q);
      $rows = array_values(array_filter($rows, static fn($r) => str_contains(strtolower(($r['full_name'] ?? '') . ' ' . ($r['phone'] ?? '') . ' ' . ($r['address'] ?? '') . ' ' . (string) ($r['id'] ?? '')), $ql)));
  }
  if ($purokFilter !== '') {
      $rows = array_values(array_filter($rows, static fn($r) => ($r['purok'] ?? '') === $purokFilter));
  }
  if ($ageFilter !== '') {
      $rows = array_values(array_filter($rows, static function ($r) use ($ageFilter) {
          return age_group(age_from_birth($r['birth_date'] ?? null)) === $ageFilter;
      }));
  }
  if ($statusFilter === 'active') {
      $rows = array_values(array_filter($rows, static fn($r) => (($r['status'] ?? '') === 'Active') || !empty($r['authorized'])));
  } elseif ($statusFilter === 'listed') {
      $rows = array_values(array_filter($rows, static fn($r) => (($r['status'] ?? '') !== 'Active') && empty($r['authorized'])));
  }
  $filteredTotal = count($rows);
  $totalPages = max(1, (int) ceil($filteredTotal / $perPage));
  $page = min($page, $totalPages);
  $pageRows = array_slice($rows, ($page - 1) * $perPage, $perPage);
  $activePct = $totalCensus ? round(($activeCensus / $totalCensus) * 100, 1) : 0;
  $pendingPct = $totalCensus ? round(($pendingCensus / $totalCensus) * 100, 1) : 0;
  $donutActive = $activePct;
  $donutPending = $activePct + $pendingPct;
  $censusActivity = db_all('SELECT * FROM activity_logs WHERE action LIKE "%resident%" OR action LIKE "%census%" OR action LIKE "%Resident%" ORDER BY id DESC LIMIT 6');
  if (!$censusActivity) {
      $censusActivity = db_all('SELECT * FROM activity_logs ORDER BY id DESC LIMIT 6');
  }
  $editId = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
  $edit = ($editId !== null && $editId > 0) ? db_one('SELECT * FROM residents WHERE id = ?', [$editId]) : null;
  $showForm = $editId !== null;
  $parts = $edit ? preg_split('/\s+/', trim($edit['full_name'])) : [];
  $censusStats = [
    ['label' => 'Total Census Records', 'value' => $totalCensus, 'color' => '#2563eb', 'bg' => '#eff6ff', 'icon' => 'census', 'trend' => 'All residents listed'],
    ['label' => 'Active / Completed', 'value' => $activeCensus, 'color' => '#16a34a', 'bg' => '#f0fdf4', 'icon' => 'registered', 'trend' => $activePct . '% of census'],
    ['label' => 'Pending Validation', 'value' => $pendingCensus, 'color' => '#d97706', 'bg' => '#fffbeb', 'icon' => 'activity', 'trend' => $pendingPct . '% pending'],
    ['label' => 'Puroks Covered', 'value' => $purokCovered, 'color' => '#7c3aed', 'bg' => '#f5f3ff', 'icon' => 'website', 'trend' => 'Distinct puroks'],
    ['label' => 'Added This Month', 'value' => $monthCensus, 'color' => '#0891b2', 'bg' => '#ecfeff', 'icon' => 'plus', 'trend' => date('F Y')],
  ];
  $qsBase = 'admin?tab=residents&q=' . urlencode($q) . '&purok=' . urlencode($purokFilter) . '&age=' . urlencode($ageFilter) . '&status=' . urlencode($statusFilter);
?>
      <?php if ($showForm): ?>
        <div class="admin-panel mb-4" id="censusForm">
          <div class="admin-panel-head">
            <div>
              <h2 class="admin-panel-title"><?= $edit ? 'Edit census record' : 'New census record' ?></h2>
              <p class="admin-panel-sub">Fill resident details for Barangay Sta. Cruz</p>
            </div>
          </div>
          <div class="p-3 p-md-4">
            <form class="row g-3" method="post">
              <input type="hidden" name="action" value="save_resident" />
              <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>" />
              <div class="col-md-4"><input class="form-control" name="first_name" required placeholder="First name" value="<?= h($parts[0] ?? '') ?>" /></div>
              <div class="col-md-4"><input class="form-control" name="middle_name" placeholder="Middle name" value="<?= h(count($parts) > 2 ? implode(' ', array_slice($parts, 1, -1)) : '') ?>" /></div>
              <div class="col-md-4"><input class="form-control" name="last_name" required placeholder="Last name" value="<?= h(count($parts) > 1 ? $parts[count($parts) - 1] : '') ?>" /></div>
              <div class="col-md-6"><input class="form-control" name="address" placeholder="Address" value="<?= h($edit['address'] ?? '') ?>" /></div>
              <div class="col-md-3">
                <select class="form-select" name="purok">
                  <?php foreach (puroks() as $pk): ?>
                    <option <?= (($edit['purok'] ?? '') === $pk) ? 'selected' : '' ?>><?= h($pk) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3"><input class="form-control" name="phone" placeholder="Phone" value="<?= h($edit['phone'] ?? '') ?>" /></div>
              <div class="col-md-3"><input class="form-control" name="birth_date" type="date" value="<?= h($edit['birth_date'] ?? '') ?>" /></div>
              <div class="col-md-3"><input class="form-control" name="occupation" placeholder="Occupation" value="<?= h($edit['occupation'] ?? '') ?>" /></div>
              <div class="col-12 d-flex gap-2">
                <button class="btn-admin-primary" type="submit">Save record</button>
                <a class="btn-admin-secondary" href="<?= h(base_url('admin?tab=residents')) ?>">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <div class="row g-3 mb-3">
        <?php foreach ($censusStats as $s): ?>
          <div class="col-6 col-md">
            <div class="admin-stat-card">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="stat-label"><?= h($s['label']) ?></div>
                <div class="admin-stat-icon" style="background:<?= h($s['bg']) ?>;color:<?= h($s['color']) ?>"><?= admin_icon($s['icon'], 16) ?></div>
              </div>
              <div class="stat-value" style="color:<?= h($s['color']) ?>"><?= number_format((int) $s['value']) ?></div>
              <div class="stat-trend"><?= h($s['trend']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="row g-3">
        <div class="col-xl-8">
          <div class="admin-panel">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">Census Records</h2>
                <p class="admin-panel-sub">Showing <?= count($pageRows) ?> of <?= (int) $filteredTotal ?> filtered · <?= (int) $totalCensus ?> total</p>
              </div>
            </div>
            <div class="p-3 border-bottom">
              <form method="get" action="<?= h(base_url('admin')) ?>" class="d-flex flex-wrap gap-2 align-items-center">
                <input type="hidden" name="tab" value="residents" />
                <select class="form-select form-select-sm" name="purok" style="max-width:150px" onchange="this.form.submit()">
                  <option value="">All Puroks</option>
                  <?php foreach (puroks() as $p): ?>
                    <option value="<?= h($p) ?>" <?= $purokFilter === $p ? 'selected' : '' ?>><?= h($p) ?></option>
                  <?php endforeach; ?>
                </select>
                <select class="form-select form-select-sm" name="status" style="max-width:150px" onchange="this.form.submit()">
                  <option value="">All Status</option>
                  <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                  <option value="listed" <?= $statusFilter === 'listed' ? 'selected' : '' ?>>Pending / Listed</option>
                </select>
                <select class="form-select form-select-sm" name="age" style="max-width:150px" onchange="this.form.submit()">
                  <option value="">All Ages</option>
                  <option value="minor" <?= $ageFilter === 'minor' ? 'selected' : '' ?>>Minor</option>
                  <option value="other" <?= $ageFilter === 'other' ? 'selected' : '' ?>>18–59</option>
                  <option value="senior" <?= $ageFilter === 'senior' ? 'selected' : '' ?>>Senior</option>
                  <option value="unknown" <?= $ageFilter === 'unknown' ? 'selected' : '' ?>>No age</option>
                </select>
                <input class="form-control form-control-sm" style="min-width:180px;max-width:240px" type="search" name="q" value="<?= h($q) ?>" placeholder="Search name, phone, ID…" />
                <button class="btn btn-sm btn-admin-primary" style="padding:.35rem .75rem !important" type="submit">Search</button>
              </form>
            </div>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <?php foreach (['Census ID', 'Resident', 'Address / Purok', 'Age', 'Phone', 'Status', 'Updated', 'Actions'] as $h): ?>
                      <th><?= h($h) ?></th>
                    <?php endforeach; ?>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!$pageRows): ?>
                    <tr><td colspan="8" class="text-center text-muted py-5">No census records found.</td></tr>
                  <?php else: foreach ($pageRows as $r):
                    $age = age_from_birth($r['birth_date'] ?? null);
                    $active = (($r['status'] ?? '') === 'Active') || !empty($r['authorized']);
                    $initial = strtoupper(substr(trim((string) ($r['full_name'] ?? 'R')), 0, 1));
                    $cid = 'CN-' . str_pad((string) $r['id'], 4, '0', STR_PAD_LEFT);
                  ?>
                    <tr>
                      <td><a class="fw-bold text-decoration-none" style="color:#2563eb" href="<?= h(base_url('admin?tab=residents&edit=' . (int) $r['id'])) ?>"><?= h($cid) ?></a></td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                            style="width:34px;height:34px;font-size:.8rem;background:#2563eb"><?= h($initial) ?></div>
                          <div>
                            <div class="fw-semibold" style="font-size:.875rem"><?= h($r['full_name']) ?></div>
                            <div class="text-muted" style="font-size:.72rem"><?= h($r['occupation'] ?: 'Resident') ?></div>
                          </div>
                        </div>
                      </td>
                      <td style="font-size:.85rem">
                        <div><?= h($r['address'] ?: '—') ?></div>
                        <div class="text-muted" style="font-size:.75rem"><?= h($r['purok'] ?: 'No purok') ?></div>
                      </td>
                      <td class="text-muted" style="font-size:.875rem"><?= $age === null ? '—' : (int) $age ?></td>
                      <td class="text-muted" style="font-size:.875rem"><?= h($r['phone'] ?: '—') ?></td>
                      <td>
                        <span class="badge rounded-pill px-2 py-1" style="<?= $active ? 'background:#dcfce7;color:#166534' : 'background:#ffedd5;color:#9a3412' ?>;font-size:.72rem">
                          <?= $active ? 'Completed' : 'Pending' ?>
                        </span>
                      </td>
                      <td class="text-muted" style="font-size:.78rem;white-space:nowrap">
                        <?= !empty($r['created_at']) ? h(date('M j, Y', strtotime($r['created_at']))) : '—' ?>
                      </td>
                      <td>
                        <div class="d-flex gap-1 align-items-center">
                          <a class="admin-icon-btn" style="width:32px;height:32px" href="<?= h(base_url('admin?tab=residents&edit=' . (int) $r['id'])) ?>" title="Edit"><?= admin_icon('pencil', 14) ?></a>
                          <form method="post" class="d-inline" onsubmit="return confirm('Create walk-in print for <?= h($r['full_name']) ?>?')">
                            <input type="hidden" name="action" value="walkin_print" />
                            <input type="hidden" name="resident_id" value="<?= (int) $r['id'] ?>" />
                            <input type="hidden" name="certificate_type" value="Barangay Clearance" />
                            <button class="admin-icon-btn" style="width:32px;height:32px;border:1px solid #bbf7d0;color:#166534" type="submit" title="Print clearance"><?= admin_icon('printer', 14) ?></button>
                          </form>
                          <form method="post" class="d-inline" onsubmit="return confirm('Remove this census record?')">
                            <input type="hidden" name="action" value="delete_resident" />
                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
                            <button class="admin-icon-btn" style="width:32px;height:32px;color:#ef4444" type="submit" title="Delete"><?= admin_icon('trash', 14) ?></button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
            <?php if ($filteredTotal > 0): ?>
              <div class="px-3 py-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="text-muted" style="font-size:.8rem">
                  Showing <?= (($page - 1) * $perPage) + 1 ?> to <?= min($page * $perPage, $filteredTotal) ?> of <?= (int) $filteredTotal ?> records
                </div>
                <div class="d-flex gap-1 flex-wrap">
                  <?php for ($p = 1; $p <= $totalPages && $p <= 8; $p++): ?>
                    <a href="<?= h(base_url($qsBase . '&page=' . $p)) ?>"
                      class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-outline-secondary' ?>"
                      style="min-width:36px;border-radius:.55rem;font-weight:700"><?= $p ?></a>
                  <?php endfor; ?>
                  <?php if ($totalPages > 8): ?>
                    <span class="text-muted px-1">…</span>
                    <a href="<?= h(base_url($qsBase . '&page=' . $totalPages)) ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:.55rem;font-weight:700"><?= $totalPages ?></a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-xl-4">
          <div class="admin-panel mb-3">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">Census by purok</h2>
                <p class="admin-panel-sub">Resident distribution</p>
              </div>
            </div>
            <div class="p-3">
              <?php if (!$purokCounts): ?>
                <p class="text-muted small mb-0">No purok data yet.</p>
              <?php else: $shown = 0; foreach ($purokCounts as $pk => $cnt): if ($shown++ >= 8) break;
                $w = ($cnt / $purokMax) * 100;
              ?>
                <div class="admin-mini-bar-row mb-2">
                  <span><?= h($pk) ?></span>
                  <div class="admin-mini-bar-track"><div class="admin-mini-bar-fill" style="width:<?= h((string) $w) ?>%"></div></div>
                  <em><?= (int) $cnt ?></em>
                </div>
              <?php endforeach; endif; ?>
            </div>
          </div>

          <div class="admin-panel mb-3">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">Survey status</h2>
                <p class="admin-panel-sub">Active vs pending records</p>
              </div>
            </div>
            <div class="p-3 d-flex flex-wrap align-items-center gap-3 justify-content-center">
              <div class="admin-donut" style="--p1:<?= h((string) $donutActive) ?>;--p2:100">
                <div class="admin-donut-center"><strong><?= number_format($totalCensus) ?></strong><span>Total</span></div>
              </div>
              <div class="admin-legend">
                <div><i style="background:#2563eb"></i> Completed <b><?= h((string) $activePct) ?>%</b></div>
                <div><i style="background:#22c55e"></i> Pending <b><?= h((string) $pendingPct) ?>%</b></div>
              </div>
            </div>
          </div>

          <div class="admin-panel">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">Recent census activity</h2>
                <p class="admin-panel-sub">Latest updates</p>
              </div>
              <a href="<?= h(base_url('admin?tab=activity')) ?>" style="font-size:.75rem;font-weight:700;color:#2563eb;text-decoration:none">View all</a>
            </div>
            <div class="px-3 pb-2">
              <?php if (!$censusActivity): ?>
                <p class="text-muted small py-3 mb-0">No activity yet.</p>
              <?php else: foreach ($censusActivity as $act): ?>
                <div class="admin-activity-item">
                  <div class="admin-activity-dot"></div>
                  <div>
                    <div class="fw-semibold" style="font-size:.84rem"><?= h($act['action'] ?? 'Action') ?></div>
                    <div class="text-muted" style="font-size:.72rem"><?= h($act['detail'] ?? '') ?></div>
                    <div class="text-muted" style="font-size:.68rem"><?= h($act['created_at'] ?? '') ?></div>
                  </div>
                </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>
      </div>

<?php elseif ($tab === 'requests'):
  $filter = $_GET['filter'] ?? 'All';
  $sort = $_GET['sort'] ?? 'date';
  $q = trim((string) ($_GET['q'] ?? ''));
  $allRows = db_all('SELECT * FROM requests ORDER BY FIELD(status,"Pending","Approved","In Progress","Completed","Rejected"), created_at DESC');
  foreach ($allRows as $i => $rr) {
      if (($rr['status'] ?? '') === 'Completed') {
          $allRows[$i] = record_certificate_issue($rr);
      }
  }
  $countAll = count($allRows);
  $countBy = ['Pending' => 0, 'In Progress' => 0, 'Completed' => 0, 'Rejected' => 0, 'Approved' => 0];
  foreach ($allRows as $rr) {
      $s = (string) ($rr['status'] ?? '');
      if (isset($countBy[$s])) {
          $countBy[$s]++;
      }
  }
  $rows = $allRows;
  if ($filter !== 'All' && $filter !== '') {
      $rows = array_values(array_filter($rows, fn($r) => $r['status'] === $filter));
  }
  if ($q !== '') {
      $ql = mb_strtolower($q);
      $rows = array_values(array_filter($rows, static function ($r) use ($ql) {
          $last = $r['last_cert'] ? json_decode((string) $r['last_cert'], true) : [];
          if (!is_array($last)) {
              $last = [];
          }
          $hay = mb_strtolower(implode(' ', [
              (string) ($r['id'] ?? ''),
              (string) ($r['resident_name'] ?? ''),
              (string) ($r['email'] ?? ''),
              (string) ($r['phone'] ?? ''),
              (string) ($r['certificate_type'] ?? ''),
              (string) ($r['purpose'] ?? ''),
              (string) ($last['controlNo'] ?? ''),
              (string) ($last['receiptNo'] ?? ''),
          ]));
          return str_contains($hay, $ql);
      }));
  }
  if ($sort === 'type') {
      usort($rows, fn($a, $b) => strcasecmp((string) $a['certificate_type'], (string) $b['certificate_type']));
  }
  $reqIds = array_map(fn($r) => (int) $r['id'], $rows);
  $logsByReq = [];
  $usersById = [];
  $residentsById = [];
  if ($reqIds) {
      $in = implode(',', array_fill(0, count($reqIds), '?'));
      foreach (db_all("SELECT * FROM request_logs WHERE request_id IN ($in) ORDER BY id DESC", $reqIds) as $log) {
          $logsByReq[(int) $log['request_id']][] = $log;
      }
  }
  $userIds = array_values(array_unique(array_filter(array_map(static fn($r) => (int) ($r['user_id'] ?? 0), $rows))));
  if ($userIds) {
      $in = implode(',', array_fill(0, count($userIds), '?'));
      foreach (db_all("SELECT id, first_name, middle_name, last_name, phone, email, street_address, purok, occupation, status, created_at FROM users WHERE id IN ($in)", $userIds) as $u) {
          $usersById[(int) $u['id']] = $u;
      }
  }
  $residentIds = array_values(array_unique(array_filter(array_map(static fn($r) => (int) ($r['resident_id'] ?? 0), $rows))));
  if ($residentIds) {
      $in = implode(',', array_fill(0, count($residentIds), '?'));
      foreach (db_all("SELECT * FROM residents WHERE id IN ($in)", $residentIds) as $res) {
          $residentsById[(int) $res['id']] = $res;
      }
  }
  $captainRow = db_one("SELECT name FROM officials WHERE position = 'Punong Barangay' LIMIT 1");
  $captainName = strtoupper(trim((string) ($captainRow['name'] ?? 'PUNONG BARANGAY')));
  $reqStats = [
    ['label' => 'Total Requests', 'value' => $countAll, 'color' => '#2563eb', 'bg' => '#eff6ff', 'icon' => 'requests', 'filter' => 'All', 'trend' => 'All time'],
    ['label' => 'Pending', 'value' => $countBy['Pending'], 'color' => '#d97706', 'bg' => '#fffbeb', 'icon' => 'activity', 'filter' => 'Pending', 'trend' => 'Needs action'],
    ['label' => 'In Progress', 'value' => $countBy['In Progress'], 'color' => '#7c3aed', 'bg' => '#f5f3ff', 'icon' => 'certificates', 'filter' => 'In Progress', 'trend' => 'Being processed'],
    ['label' => 'Completed', 'value' => $countBy['Completed'], 'color' => '#16a34a', 'bg' => '#f0fdf4', 'icon' => 'registered', 'filter' => 'Completed', 'trend' => 'Printed / released'],
    ['label' => 'Rejected', 'value' => $countBy['Rejected'], 'color' => '#dc2626', 'bg' => '#fef2f2', 'icon' => 'xcircle', 'filter' => 'Rejected', 'trend' => 'Not approved'],
  ];
  $reqMax = max(1, $countBy['Pending'], $countBy['In Progress'], $countBy['Completed'], $countBy['Rejected']);
  $reqTotalStatus = max(1, $countBy['Pending'] + $countBy['In Progress'] + $countBy['Completed'] + $countBy['Rejected']);
  $pPend = round(($countBy['Pending'] / $reqTotalStatus) * 100, 1);
  $pProg = round(($countBy['In Progress'] / $reqTotalStatus) * 100, 1);
  $pDone = round(($countBy['Completed'] / $reqTotalStatus) * 100, 1);
  $reqActivity = db_all('SELECT * FROM activity_logs WHERE action LIKE "%request%" OR action LIKE "%Print%" OR action LIKE "%cert%" ORDER BY id DESC LIMIT 5');
  if (!$reqActivity) {
      $reqActivity = db_all('SELECT * FROM activity_logs ORDER BY id DESC LIMIT 5');
  }
?>
      <div class="row g-3 mb-3">
        <?php foreach ($reqStats as $s): ?>
          <div class="col-6 col-md">
            <a href="<?= h(base_url('admin?tab=requests&filter=' . urlencode($s['filter']) . '&sort=' . urlencode($sort) . ($q !== '' ? '&q=' . urlencode($q) : ''))) ?>" class="text-decoration-none">
              <div class="admin-stat-card <?= $filter === $s['filter'] ? 'border-primary' : '' ?>">
                <div class="d-flex justify-content-between align-items-start gap-2">
                  <div class="stat-label"><?= h($s['label']) ?></div>
                  <div class="admin-stat-icon" style="background:<?= h($s['bg']) ?>;color:<?= h($s['color']) ?>"><?= admin_icon($s['icon'], 16) ?></div>
                </div>
                <div class="stat-value" style="color:<?= h($s['color']) ?>"><?= number_format((int) $s['value']) ?></div>
                <div class="stat-trend"><?= h($s['trend']) ?></div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="row g-3">
        <div class="col-12">
      <div class="admin-panel mb-0">
        <div class="admin-panel-head">
          <div>
            <h2 class="admin-panel-title">Requests Directory</h2>
            <p class="admin-panel-sub">Showing <?= count($rows) ?> of <?= (int) $countAll ?> requests</p>
          </div>
        </div>
        <div class="p-3 border-bottom">
          <div class="d-flex gap-2 flex-wrap align-items-center">
            <?php foreach (['All' => 'All', 'Pending' => 'Pending', 'In Progress' => 'In Progress', 'Completed' => 'Completed', 'Rejected' => 'Rejected'] as $val => $label):
              $n = $val === 'All' ? $countAll : ($countBy[$val] ?? 0);
            ?>
              <a href="<?= h(base_url('admin?tab=requests&filter=' . urlencode($val) . '&sort=' . urlencode($sort) . ($q !== '' ? '&q=' . urlencode($q) : ''))) ?>"
                class="btn btn-sm rounded-pill fw-semibold <?= $filter === $val ? 'btn-primary' : 'btn-outline-secondary' ?>"
                style="font-size:.78rem"><?= h($label) ?> (<?= (int) $n ?>)</a>
            <?php endforeach; ?>
            <form method="get" action="<?= h(base_url('admin')) ?>" class="d-flex flex-wrap gap-2 align-items-center ms-md-auto">
              <input type="hidden" name="tab" value="requests" />
              <input type="hidden" name="filter" value="<?= h($filter) ?>" />
              <input class="form-control form-control-sm" style="min-width:180px;max-width:220px" type="search" name="q"
                value="<?= h($q) ?>" placeholder="Search…" />
              <select class="form-select form-select-sm" name="sort" style="max-width:140px" onchange="this.form.submit()">
                <option value="date" <?= $sort === 'date' ? 'selected' : '' ?>>Sort by date</option>
                <option value="type" <?= $sort === 'type' ? 'selected' : '' ?>>Sort by type</option>
              </select>
              <button class="btn btn-sm btn-admin-primary" style="padding:.35rem .7rem !important" type="submit">Search</button>
            </form>
          </div>
        </div>
        <div class="overflow-hidden">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 req-table w-100">
            <thead class="table-light">
              <tr>
                <?php foreach (['Ref', 'Resident', 'Certificate', 'Fee / Payment', 'Issued', 'Date', 'Status', 'Actions'] as $h): ?>
                  <th style="font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;color:#64748b"><?= h($h) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php if (!$rows): ?>
                <tr><td colspan="8" class="text-center text-muted py-5">
                  <div class="fw-semibold mb-1">No requests found</div>
                  <div style="font-size:.85rem">Try another filter or clear the search.</div>
                </td></tr>
              <?php else: foreach ($rows as $r):
                $rid = (int) $r['id'];
                $st = (string) ($r['status'] ?? 'Pending');
                $last = $r['last_cert'] ? json_decode($r['last_cert'], true) : [];
                if (!is_array($last)) $last = [];
                $control = (string) ($last['controlNo'] ?? '');
                $receiptNo = (string) ($last['receiptNo'] ?? ($control !== '' ? 'OR-' . $control : ''));
                $amount = (string) ($last['amount'] ?? service_price($site, (string) $r['certificate_type']));
                $email = trim((string) ($r['email'] ?? ''));
                $phone = trim((string) ($r['phone'] ?? ''));
                $contact = $email !== '' ? $email : ($phone !== '' ? $phone : '—');
                $dateLabel = $r['created_at'] ? date('M j, Y', strtotime($r['created_at'])) : '—';
                $timeLabel = $r['created_at'] ? date('g:i A', strtotime($r['created_at'])) : '';
                $note = trim((string) ($r['admin_note'] ?? ''));
                $emailed = !empty($r['status_email_sent_at']);
              ?>
                <tr>
                  <td style="white-space:nowrap">
                    <div class="fw-bold" style="color:#0038A8;font-size:.9rem">#<?= $rid ?></div>
                    <?php if (!empty($r['walk_in'])): ?>
                      <span class="badge rounded-pill mt-1" style="background:#e2e8f0;color:#334155;font-size:.65rem">Walk-in</span>
                    <?php else: ?>
                      <div class="text-muted" style="font-size:.68rem">Online</div>
                    <?php endif; ?>
                  </td>
                  <td style="min-width:180px">
                    <div class="fw-semibold" style="font-size:.875rem"><?= h($r['resident_name']) ?></div>
                    <?php if ($email !== ''): ?>
                      <div class="text-muted" style="font-size:.75rem"><?= h($email) ?></div>
                    <?php endif; ?>
                    <?php if ($phone !== ''): ?>
                      <div class="text-muted" style="font-size:.75rem"><?= h($phone) ?></div>
                    <?php endif; ?>
                    <?php if ($email === '' && $phone === ''): ?>
                      <div class="text-muted" style="font-size:.75rem">No contact on file</div>
                    <?php endif; ?>
                  </td>
                  <td style="min-width:170px;font-size:.875rem">
                    <div class="fw-semibold"><?= h($r['certificate_type']) ?></div>
                    <?php if ($r['purpose']): ?>
                      <div class="text-muted" style="font-size:.75rem">Purpose: <?= h($r['purpose']) ?></div>
                    <?php endif; ?>
                    <?php if ($note !== ''): ?>
                      <div class="mt-1" style="font-size:.72rem;color:#9a3412;background:#fff7ed;border-radius:.4rem;padding:.15rem .4rem;display:inline-block">Note: <?= h($note) ?></div>
                    <?php endif; ?>
                  </td>
                  <td style="font-size:.85rem;white-space:nowrap">
                    <div class="fw-semibold"><?= h($amount !== '' ? $amount : '—') ?></div>
                    <div class="text-muted" style="font-size:.75rem"><?= h($r['payment_mode'] ?: 'Pay at Barangay Hall') ?></div>
                  </td>
                  <td style="font-size:.8rem;min-width:140px">
                    <?php if ($control !== ''): ?>
                      <div><span class="text-muted">Ctrl</span> <strong><?= h($control) ?></strong></div>
                      <?php if ($receiptNo !== ''): ?>
                        <div class="text-muted" style="font-size:.72rem">OR <?= h($receiptNo) ?></div>
                      <?php endif; ?>
                      <?php if ($emailed): ?>
                        <div style="font-size:.68rem;color:#166534">Email sent</div>
                      <?php endif; ?>
                    <?php elseif ($st === 'Completed'): ?>
                      <div class="fw-semibold" style="font-size:.8rem;color:#166534">Issued</div>
                    <?php else: ?>
                      <span class="text-muted" style="font-size:.75rem">Not printed yet</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-muted" style="font-size:.8rem;white-space:nowrap">
                    <div><?= h($dateLabel) ?></div>
                    <?php if ($timeLabel !== ''): ?>
                      <div style="font-size:.72rem"><?= h($timeLabel) ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge rounded-pill px-2 py-1" style="<?= h(status_badge_style($st)) ?>;font-size:.75rem"><?= h($st) ?></span>
                  </td>
                  <td>
                    <button type="button" class="btn btn-sm rounded-pill fw-semibold d-inline-flex align-items-center gap-1"
                      style="background:#0038A8;color:#fff;font-size:.75rem;border:none"
                      data-bs-toggle="modal" data-bs-target="#logsModal<?= $rid ?>">
                      <?= admin_icon('history', 12) ?> View request
                    </button>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
        </div>
      </div>

      <div class="row g-3 mt-1">
        <div class="col-lg-4">
          <div class="admin-panel mb-0 h-100">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">Requests by status</h2>
                <p class="admin-panel-sub">Current queue breakdown</p>
              </div>
            </div>
            <div class="p-3">
              <div class="admin-mini-bar">
                <?php foreach ([
                  ['Pending', $countBy['Pending'], '#f59e0b'],
                  ['In Progress', $countBy['In Progress'], '#3b82f6'],
                  ['Completed', $countBy['Completed'], '#22c55e'],
                  ['Rejected', $countBy['Rejected'], '#ef4444'],
                ] as [$label, $count, $color]):
                  $w = ((int) $count / $reqMax) * 100;
                ?>
                  <div class="admin-mini-bar-row">
                    <span><?= h($label) ?></span>
                    <div class="admin-mini-bar-track"><div class="admin-mini-bar-fill" style="width:<?= h((string) $w) ?>%;background:<?= h($color) ?>"></div></div>
                    <em><?= (int) $count ?></em>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

        </div>
        <div class="col-lg-4">
          <div class="admin-panel mb-0 h-100">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">Status mix</h2>
                <p class="admin-panel-sub">Share of all requests</p>
              </div>
            </div>
            <div class="p-3 d-flex flex-wrap align-items-center gap-3 justify-content-center">
              <div class="admin-donut admin-donut-status" style="--p1:<?= h((string) $pPend) ?>;--p2:<?= h((string) ($pPend + $pProg)) ?>;--p3:<?= h((string) ($pPend + $pProg + $pDone)) ?>">
                <div class="admin-donut-center"><strong><?= (int) $countAll ?></strong><span>Total</span></div>
              </div>
              <div class="admin-legend">
                <div><i style="background:#f59e0b"></i> Pending <b><?= h((string) $pPend) ?>%</b></div>
                <div><i style="background:#3b82f6"></i> In Progress <b><?= h((string) $pProg) ?>%</b></div>
                <div><i style="background:#22c55e"></i> Completed <b><?= h((string) $pDone) ?>%</b></div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="admin-panel mb-0 h-100">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">Recent activity</h2>
                <p class="admin-panel-sub">Print &amp; status updates</p>
              </div>
              <a href="<?= h(base_url('admin?tab=activity')) ?>" style="font-size:.75rem;font-weight:700;color:#2563eb;text-decoration:none">View all</a>
            </div>
            <div class="px-3 pb-2">
              <?php if (!$reqActivity): ?>
                <p class="text-muted small py-3 mb-0">No activity yet.</p>
              <?php else: foreach ($reqActivity as $act): ?>
                <div class="admin-activity-item">
                  <div class="admin-activity-dot"></div>
                  <div>
                    <div class="fw-semibold" style="font-size:.84rem"><?= h($act['action'] ?? 'Action') ?></div>
                    <div class="text-muted" style="font-size:.72rem"><?= h($act['detail'] ?? '') ?></div>
                    <div class="text-muted" style="font-size:.68rem"><?= h($act['created_at'] ?? '') ?></div>
                  </div>
                </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>
      </div>

      <?php if ($rows): foreach ($rows as $r):
        $rid = (int) $r['id'];
        $st = (string) ($r['status'] ?? 'Pending');
        $last = $r['last_cert'] ? json_decode($r['last_cert'], true) : [];
        if (!is_array($last)) $last = [];
        $control = (string) ($last['controlNo'] ?? '');
        $receiptNo = (string) ($last['receiptNo'] ?? ($control !== '' ? 'OR-' . $control : 'OR-REQ-' . $rid));
        $amount = (string) ($last['amount'] ?? service_price($site, (string) $r['certificate_type']));
        $email = trim((string) ($r['email'] ?? ''));
        $phone = trim((string) ($r['phone'] ?? ''));
        $contact = $email !== '' ? $email : ($phone !== '' ? $phone : '—');
        $dateLabel = $r['created_at'] ? date('M j, Y', strtotime($r['created_at'])) : '—';
        $timeLabel = $r['created_at'] ? date('g:i A', strtotime($r['created_at'])) : '';
        $note = trim((string) ($r['admin_note'] ?? ''));
        $logs = $logsByReq[$rid] ?? [];
        $person = $usersById[(int) ($r['user_id'] ?? 0)] ?? null;
        $resident = $residentsById[(int) ($r['resident_id'] ?? 0)] ?? null;
        $fullName = trim(implode(' ', array_filter([
            $person['first_name'] ?? '',
            $person['middle_name'] ?? '',
            $person['last_name'] ?? '',
        ])));
        if ($fullName === '') {
            $fullName = (string) ($resident['full_name'] ?? $r['resident_name'] ?? '—');
        }
        $personPhone = trim((string) ($person['phone'] ?? $resident['phone'] ?? $phone));
        $personEmail = trim((string) ($person['email'] ?? $email));
        $personAddress = trim((string) ($person['street_address'] ?? $resident['address'] ?? ''));
        $personPurok = trim((string) ($person['purok'] ?? $resident['purok'] ?? ''));
        $personJob = trim((string) ($person['occupation'] ?? $resident['occupation'] ?? ''));
        $personBirth = trim((string) ($resident['birth_date'] ?? ''));
        $personStatus = trim((string) ($person['status'] ?? $resident['status'] ?? ''));
      ?>
        <div class="modal fade" id="logsModal<?= $rid ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0">
              <div class="modal-header" style="background:linear-gradient(135deg,#0038A8,#002570);color:#fff">
                <h5 class="modal-title fw-bold">Request #<?= $rid ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <h6 class="fw-bold mb-2" style="color:#0038A8">Resident</h6>
                <div class="rounded-3 p-3 mb-3" style="background:#f8fafc;border:1px solid #e2e8f0;font-size:.85rem">
                  <div class="row g-2">
                    <?php foreach ([
                      'Name' => $fullName,
                      'Email' => $personEmail !== '' ? $personEmail : '—',
                      'Mobile' => $personPhone !== '' ? $personPhone : '—',
                      'Address' => $personAddress !== '' ? $personAddress : '—',
                      'Purok' => $personPurok !== '' ? $personPurok : '—',
                      'Occupation' => $personJob !== '' ? $personJob : '—',
                      'Birth date' => $personBirth !== '' ? $personBirth : '—',
                      'Account' => $personStatus !== '' ? ucfirst($personStatus) : '—',
                    ] as $label => $value): ?>
                      <div class="col-md-6">
                        <div class="text-muted" style="font-size:.72rem"><?= h($label) ?></div>
                        <div class="fw-semibold"><?= h($value) ?></div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>

                <h6 class="fw-bold mb-2" style="color:#0038A8">Request</h6>
                <div class="rounded-3 p-3 mb-3" style="background:#fff;border:1px solid #e2e8f0;font-size:.85rem">
                  <div class="row g-2">
                    <?php foreach ([
                      'Reference' => '#' . $rid . (!empty($r['walk_in']) ? ' · Walk-in' : ' · Online'),
                      'Certificate' => (string) ($r['certificate_type'] ?? '—'),
                      'Purpose' => (string) (($r['purpose'] ?? '') !== '' ? $r['purpose'] : '—'),
                      'Status' => $st,
                      'Fee' => $amount !== '' ? $amount : '—',
                      'Payment' => (string) ($r['payment_mode'] ?: 'Pay at Barangay Hall'),
                      'Filed' => trim($dateLabel . ' ' . $timeLabel),
                      'Control No.' => $control !== '' ? $control : 'Not printed yet',
                      'Receipt' => $control !== '' ? $receiptNo : '—',
                      'Note' => $note !== '' ? $note : '—',
                    ] as $label => $value): ?>
                      <div class="col-md-6">
                        <div class="text-muted" style="font-size:.72rem"><?= h($label) ?></div>
                        <div class="fw-semibold"><?= h($value) ?></div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="<?= h(base_url('print-cert?id=' . $rid)) ?>" class="btn btn-sm rounded-pill fw-semibold" style="background:#0038A8;color:#fff"><?= admin_icon('printer', 12) ?> <?= $st === 'Completed' ? 'Print again' : 'View & print' ?></a>
                    <?php if ($control !== ''): ?>
                      <button type="button" class="btn btn-sm rounded-pill fw-semibold" style="background:#0f766e;color:#fff" data-bs-toggle="modal" data-bs-target="#receiptModal<?= $rid ?>"><?= admin_icon('receipt', 12) ?> Receipt</button>
                    <?php endif; ?>
                  </div>
                </div>

                <h6 class="fw-bold mb-2" style="color:#0038A8">History</h6>
                <?php if (!$logs): ?>
                  <p class="text-muted mb-0">No status history yet.</p>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                      <thead><tr><th>When</th><th>Action</th><th>Detail</th><th>By</th></tr></thead>
                      <tbody>
                        <?php foreach ($logs as $log): ?>
                          <tr>
                            <td class="text-muted small text-nowrap"><?= h($log['created_at']) ?></td>
                            <td class="fw-semibold small"><?= h($log['action']) ?></td>
                            <td class="small"><?= h($log['detail'] ?: '—') ?></td>
                            <td class="text-muted small"><?= h($log['actor'] ?: '—') ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
                <hr>
                <form method="post" class="row g-2 align-items-end">
                  <input type="hidden" name="action" value="update_request" />
                  <input type="hidden" name="id" value="<?= $rid ?>" />
                  <input type="hidden" name="return_filter" value="<?= h($filter) ?>" />
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Update status</label>
                    <select class="form-select form-select-sm" name="status">
                      <?php foreach (['Pending', 'Approved', 'In Progress', 'Completed', 'Rejected'] as $s): ?>
                        <option <?= $st === $s ? 'selected' : '' ?>><?= $s ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-5">
                    <label class="form-label small fw-semibold mb-1">Note</label>
                    <input class="form-control form-control-sm" name="admin_note" value="<?= h($r['admin_note'] ?? '') ?>" placeholder="Optional note" />
                  </div>
                  <div class="col-md-3">
                    <button class="btn btn-sm fw-bold w-100" style="background:#0038A8;color:#fff" type="submit">Save &amp; email</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="receiptModal<?= $rid ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0">
              <div class="modal-header" style="background:linear-gradient(135deg,#0038A8,#002570);color:#fff">
                <h5 class="modal-title fw-bold">Official Receipt</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="receipt-sheet" id="receiptSheet<?= $rid ?>">
                  <p class="receipt-kicker">Barangay Sta. Cruz · Bacnotan, La Union</p>
                  <h3>Official Receipt</h3>
                  <dl>
                    <?php foreach ([
                      'Receipt No.' => $receiptNo,
                      'Received from' => $last['recipientName'] ?? $r['resident_name'],
                      'Contact' => $contact,
                      'Certificate' => $last['type'] ?? $r['certificate_type'],
                      'Purpose' => $last['purpose'] ?? $r['purpose'],
                      'Payment' => $r['payment_mode'] ?: 'Pay at Barangay Hall',
                      'Amount' => $amount ?: '—',
                      'Date' => date('F j, Y'),
                      'Issued by' => $last['certifiedBy'] ?? $captainName,
                      'Status' => $st,
                      'Control No.' => $control ?: '—',
                    ] as $label => $value): ?>
                      <div class="receipt-row">
                        <dt><?= h($label) ?></dt>
                        <dd><?= h((string) ($value ?: '—')) ?></dd>
                      </div>
                    <?php endforeach; ?>
                  </dl>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-3 fw-bold" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn rounded-3 fw-bold text-white" style="background:#0f766e"
                  onclick="printReceiptSheet(<?= $rid ?>)"><?= admin_icon('printer', 14) ?> Print receipt</button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; endif; ?>

      <style>
        .req-table tbody tr td { vertical-align: top; padding-top: .9rem; padding-bottom: .9rem; }
        .req-table thead th { border-bottom-width: 1px; }
        .receipt-sheet {
          border: 1px solid #cbd5e1;
          border-radius: .75rem;
          padding: 1.1rem 1.2rem;
          background: #fff;
          font-family: Georgia, 'Times New Roman', serif;
        }
        .receipt-kicker {
          margin: 0 0 .35rem;
          font-size: .72rem;
          letter-spacing: .08em;
          text-transform: uppercase;
          color: #64748b;
          font-family: Segoe UI, Arial, sans-serif;
        }
        .receipt-sheet h3 {
          margin: 0 0 1rem;
          color: #0038A8;
          font-size: 1.25rem;
          text-align: center;
        }
        .receipt-sheet dl { margin: 0; }
        .receipt-row {
          display: grid;
          grid-template-columns: 8rem 1fr;
          gap: .5rem;
          padding: .4rem 0;
          border-bottom: 1px dashed #e2e8f0;
          font-size: .92rem;
        }
        .receipt-row dt { margin: 0; color: #64748b; font-weight: 600; font-family: Segoe UI, Arial, sans-serif; font-size: .8rem; }
        .receipt-row dd { margin: 0; color: #0f172a; }
        @media print {
          body.printing-receipt * { visibility: hidden !important; }
          body.printing-receipt .receipt-print-target,
          body.printing-receipt .receipt-print-target * { visibility: visible !important; }
          body.printing-receipt .receipt-print-target {
            position: absolute; left: 0; top: 0; width: 100%;
            border: none !important; box-shadow: none !important;
          }
        }
      </style>
      <script>
      function printReceiptSheet(id) {
        var sheet = document.getElementById('receiptSheet' + id);
        if (!sheet) return;
        sheet.classList.add('receipt-print-target');
        document.body.classList.add('printing-receipt');
        window.print();
        setTimeout(function () {
          document.body.classList.remove('printing-receipt');
          sheet.classList.remove('receipt-print-target');
        }, 300);
      }
      </script>

<?php elseif ($tab === 'activity'):
  $logs = db_all('SELECT * FROM activity_logs ORDER BY id DESC LIMIT 200');
?>
      <div class="bg-white rounded-4 shadow-sm border overflow-x-auto">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <?php foreach (['When', 'Action', 'Detail', 'By'] as $h): ?>
                  <th style="font-size:.75rem;text-transform:uppercase"><?= h($h) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php if (!$logs): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">No activity yet.</td></tr>
              <?php else: foreach ($logs as $l): ?>
                <tr>
                  <td class="text-muted" style="font-size:.875rem;white-space:nowrap"><?= h($l['created_at']) ?></td>
                  <td class="fw-semibold" style="font-size:.875rem"><?= h($l['action']) ?></td>
                  <td style="font-size:.875rem"><?= h($l['detail']) ?></td>
                  <td class="text-muted" style="font-size:.875rem"><?= h($l['actor']) ?> <span>(<?= h($l['actor_role']) ?>)</span></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

<?php elseif ($tab === 'certificates'):
  $types = cert_types();
  $cur = trim((string) ($_GET['type'] ?? ''));
  $showPreview = $cur !== '' && in_array($cur, $types, true);
  if ($cur !== '' && !in_array($cur, $types, true)) {
      $showPreview = false;
      $cur = '';
  }
  $tpl = $showPreview ? get_cert_template($cur) : default_cert_template();
  $captain = db_one("SELECT name FROM officials WHERE position = 'Punong Barangay' LIMIT 1");
  $certified = strtoupper(trim((string) ($captain['name'] ?? '')));
  $issued = issued_today();
  $receiptPrice = $showPreview ? service_price($site, $cur) : '—';
  $receiptDate = date('F j, Y');
  $previewControl = $showPreview ? make_control_no() : '';
?>
      <div class="row g-4 cert-admin-layout">
        <div class="col-12 col-lg-4">
          <div class="bg-white rounded-4 shadow-sm border p-4">
            <h6 class="fw-bold mb-3">📋 Certificate Templates</h6>
            <p class="text-muted small mb-3">Edits save when you click Save now. They are used the next time a certificate is printed.</p>

            <div class="d-flex flex-column gap-2">
              <?php foreach ($types as $type): ?>
                <div class="d-flex gap-1">
                  <a href="<?= h(base_url('admin?tab=certificates&type=' . urlencode($type))) ?>"
                    class="btn text-start fw-medium rounded-3 py-2 flex-fill <?= ($showPreview && $cur === $type) ? 'btn-primary-custom' : 'btn-outline-secondary' ?>"
                    style="font-size:.9rem"><?= h($type) ?></a>
                  <form method="post" onsubmit="return confirm('Remove this certificate template?')">
                    <input type="hidden" name="action" value="delete_template" />
                    <input type="hidden" name="type" value="<?= h($type) ?>" />
                    <button type="submit" class="btn btn-sm" style="color:#ef4444" title="Remove certificate"><?= admin_icon('trash', 14) ?></button>
                  </form>
                </div>
              <?php endforeach; ?>
            </div>

            <form method="post" class="d-flex gap-2 mt-3">
              <input type="hidden" name="action" value="add_template" />
              <input class="form-control form-control-sm" name="type" placeholder="New certificate name" required />
              <button type="submit" class="btn btn-primary-custom btn-sm fw-bold rounded-3 text-nowrap">
                <?= admin_icon('plus', 14) ?> Add
              </button>
            </form>

            <?php if ($showPreview): ?>
              <hr class="my-4" />
              <h6 class="fw-bold mb-3">Edit Template</h6>
              <form method="post" id="certEditForm" class="d-flex flex-column gap-3">
                <input type="hidden" name="action" value="save_template" />
                <input type="hidden" name="type" id="certTypeField" value="<?= h($cur) ?>" />
                <div>
                  <label class="form-label fw-medium small">Certificate wording</label>
                  <textarea class="form-control" name="body" id="certBody" rows="5"
                    placeholder="Keep RESIDENT NAME and ADDRESS in the text. They are filled in when printing."><?= h($tpl['body']) ?></textarea>
                </div>
                <div>
                  <label class="form-label fw-medium small">Default Purpose/Message</label>
                  <textarea class="form-control" name="purpose" id="certPurpose" rows="3"
                    placeholder="Default certificate purpose text"><?= h($tpl['purpose']) ?></textarea>
                </div>
                <div>
                  <label class="form-label fw-medium small">Additional Details (Optional)</label>
                  <textarea class="form-control" name="details" id="certDetails" rows="2"
                    placeholder="Additional template information"><?= h($tpl['details']) ?></textarea>
                </div>
                <input type="hidden" name="closing" id="certClosing" value="<?= h($tpl['closing']) ?>" />
                <p class="text-muted mb-0" style="font-size:.8rem">Click Save now to keep wording for the next print.</p>
                <button type="submit" class="btn fw-bold rounded-3" style="background:#22c55e;color:#fff">Save now</button>
                <a href="<?= h(base_url('admin?tab=certificates')) ?>" class="btn btn-outline-secondary fw-bold rounded-3">Cancel</a>
              </form>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-12 col-lg-8">
          <?php if ($showPreview): ?>
            <div class="bg-white rounded-4 shadow-sm border p-4">
              <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h6 class="fw-bold mb-0">Template Preview</h6>
                <div class="d-flex gap-2">
                  <button type="button" id="certPrintBtn" class="btn btn-sm fw-bold d-flex align-items-center gap-1"
                    style="background:#003d82;color:#fff"><?= admin_icon('printer', 14) ?> Print</button>
                  <a href="<?= h(base_url('admin?tab=certificates')) ?>" class="btn btn-sm btn-outline-secondary">Close</a>
                </div>
              </div>
              <p class="text-muted small mb-2">Click any text to edit it live on the certificate</p>
              <div class="cert-print-area" id="certPrintArea" style="max-height:600px;overflow-y:auto;border:1px solid #dee2e6;border-radius:.5rem;padding:1rem;background:#f8fafc">
                <?php
                  $cert = [
                    'type' => $cur,
                    'body' => $tpl['body'],
                    'purpose' => $tpl['purpose'],
                    'details' => $tpl['details'],
                    'closing' => $tpl['closing'],
                    'issuedDate' => $issued,
                    'certifiedBy' => $certified,
                    'controlNo' => $previewControl,
                    'receiptNo' => '',
                    'receiptPrice' => $receiptPrice ?: '—',
                    'receiptDate' => $receiptDate,
                    'recipientName' => 'RESIDENT NAME',
                    'address' => 'Barangay Sta. Cruz, Bacnotan, La Union',
                  ];
                  $certEditable = true;
                  $certIds = true;
                  require __DIR__ . '/_cert_template.php';
                ?>
              </div>
            </div>
          <?php else: ?>
            <div class="bg-white rounded-4 shadow-sm border p-4 text-center" style="min-height:400px;display:flex;align-items:center;justify-content:center">
              <div>
                <div style="color:#003d82;margin-bottom:1rem"><?= admin_icon('certificates', 48) ?></div>
                <p class="text-muted mb-1">Select a certificate template from the left to edit and save</p>
                <p class="small text-muted mb-0">Edits are used the next time a certificate is printed</p>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <?php if ($showPreview): ?>
      <script>
      (function () {
        var map = {
          body: ['certBody', 'pvBody'],
          purpose: ['certPurpose', 'pvPurpose'],
          details: ['certDetails', 'pvDetails'],
          closing: ['certClosing', 'pvClosing'],
          type: ['certTypeField', 'pvType']
        };
        function syncFromInputs() {
          Object.keys(map).forEach(function (key) {
            var input = document.getElementById(map[key][0]);
            var preview = document.getElementById(map[key][1]);
            if (!input || !preview) return;
            preview.textContent = input.value;
          });
        }
        Object.keys(map).forEach(function (key) {
          var input = document.getElementById(map[key][0]);
          var preview = document.getElementById(map[key][1]);
          if (input && key !== 'type') {
            input.addEventListener('input', function () {
              if (preview) preview.textContent = input.value;
            });
          }
          if (preview) {
            preview.addEventListener('blur', function () {
              if (!input) return;
              input.value = preview.innerText.trim();
              if (key === 'type') {
                var form = document.getElementById('certEditForm');
                if (form) {
                  // keep type field for save; rename only after save
                }
              }
            });
          }
        });
        var printBtn = document.getElementById('certPrintBtn');
        if (printBtn) {
          printBtn.addEventListener('click', function () {
            document.body.classList.add('printing-cert-preview');
            window.print();
            setTimeout(function () { document.body.classList.remove('printing-cert-preview'); }, 300);
          });
        }
      })();
      </script>
      <style>
        @media print {
          body.printing-cert-preview #sidebar,
          body.printing-cert-preview .admin-topbar,
          body.printing-cert-preview .sidebar-backdrop,
          body.printing-cert-preview .flash,
          body.printing-cert-preview .col-lg-4,
          body.printing-cert-preview .admin-content > .admin-topbar { display: none !important; }
          body.printing-cert-preview .admin-content { margin-left: 0 !important; }
          body.printing-cert-preview .cert-print-area {
            max-height: none !important;
            overflow: visible !important;
            border: none !important;
            padding: 0 !important;
            background: #fff !important;
          }
          body.printing-cert-preview .cert-sheet { box-shadow: none !important; }
        }
      </style>
      <?php endif; ?>

<?php elseif ($tab === 'announcements'):
  $rows = db_all('SELECT * FROM announcements ORDER BY created_at DESC');
?>
      <div class="bg-white rounded-4 shadow-sm border p-4 mb-3">
        <form method="post" class="row g-3">
          <input type="hidden" name="action" value="save_announcement" />
          <div class="col-12"><input class="form-control" name="title" required placeholder="Title" /></div>
          <div class="col-12"><textarea class="form-control" name="body" rows="4" placeholder="Body"></textarea></div>
          <div class="col-12"><button class="btn fw-semibold" style="background:#003d82;color:#fff" type="submit">Post</button></div>
        </form>
      </div>
      <?php foreach ($rows as $r): ?>
        <div class="bg-white rounded-4 shadow-sm border p-4 mb-3">
          <strong><?= h($r['title']) ?></strong>
          <p class="mb-2 mt-2"><?= nl2br(h($r['body'])) ?></p>
          <form method="post">
            <input type="hidden" name="action" value="delete_announcement" />
            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
            <button class="btn btn-outline-danger btn-sm" type="submit">Delete</button>
          </form>
        </div>
      <?php endforeach; ?>

<?php elseif ($tab === 'officials'):
  $rows = db_all('SELECT * FROM officials ORDER BY order_num, name');
?>
      <div class="bg-white rounded-4 shadow-sm border p-4 mb-3">
        <p class="text-muted small">Add only real officials with a photo.</p>
        <form method="post" enctype="multipart/form-data" class="row g-3">
          <input type="hidden" name="action" value="save_official" />
          <div class="col-md-4"><input class="form-control" name="name" required placeholder="Full name" /></div>
          <div class="col-md-4">
            <select class="form-select" name="position">
              <?php foreach (positions() as $p): ?><option><?= h($p) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4"><input class="form-control" name="order_num" type="number" min="1" max="7" placeholder="Kagawad order 1-7" /></div>
          <div class="col-12"><textarea class="form-control" name="description" placeholder="Description"></textarea></div>
          <div class="col-md-6"><input class="form-control" type="file" name="photo" accept="image/*" required /></div>
          <div class="col-12"><button class="btn fw-semibold" style="background:#003d82;color:#fff" type="submit">Add official</button></div>
        </form>
      </div>
      <div class="d-flex flex-wrap gap-3">
        <?php foreach ($rows as $o): ?>
          <div class="bg-white rounded-4 shadow-sm border p-3 text-center" style="width:180px">
            <?php if ($o['photo_url']): ?>
              <img src="<?= h(base_url($o['photo_url'])) ?>" alt="" style="width:96px;height:96px;object-fit:cover;border-radius:50%;margin-bottom:8px" />
            <?php endif; ?>
            <div class="fw-semibold"><?= h($o['name']) ?></div>
            <div class="text-muted small mb-2"><?= h($o['position']) ?></div>
            <form method="post">
              <input type="hidden" name="action" value="delete_official" />
              <input type="hidden" name="id" value="<?= (int) $o['id'] ?>" />
              <button class="btn btn-outline-secondary btn-sm" type="submit">Remove</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>

<?php elseif ($tab === 'website'): ?>
      <div class="bg-white rounded-4 shadow-sm border p-4">
        <form method="post" class="row g-3">
          <input type="hidden" name="action" value="save_site" />
          <div class="col-md-6"><label class="form-label fw-medium">Emergency call</label><input class="form-control" name="emergencyCall" value="<?= h($site['emergencyCall'] ?? '') ?>" /></div>
          <div class="col-12"><label class="form-label fw-medium">Emergency lead</label><textarea class="form-control" name="emergencyLead" rows="3"><?= h($site['emergencyLead'] ?? '') ?></textarea></div>
          <div class="col-12"><label class="form-label fw-medium">Office address</label><textarea class="form-control" name="officeAddress" rows="2"><?= h($site['officeAddress'] ?? '') ?></textarea></div>
          <div class="col-md-6"><label class="form-label fw-medium">Contact phones</label><textarea class="form-control" name="contactPhones" rows="2"><?= h($site['contactPhones'] ?? '') ?></textarea></div>
          <div class="col-md-6"><label class="form-label fw-medium">Contact email</label><input class="form-control" name="contactEmail" value="<?= h($site['contactEmail'] ?? '') ?>" /></div>
          <div class="col-md-6"><label class="form-label fw-medium">Office hours</label><textarea class="form-control" name="officeHours" rows="2"><?= h($site['officeHours'] ?? '') ?></textarea></div>
          <div class="col-md-6"><label class="form-label fw-medium">Footer location</label><input class="form-control" name="footerLocation" value="<?= h($site['footerLocation'] ?? '') ?>" /></div>
          <div class="col-12"><h6 class="fw-bold mb-0 mt-2">Service prices</h6></div>
          <?php foreach ($site['services'] ?? [] as $i => $s): ?>
            <div class="col-12 border rounded-3 p-3">
              <strong><?= h($s['type']) ?></strong>
              <div class="row g-2 mt-1">
                <div class="col-md-4"><input class="form-control" name="price[<?= $i ?>]" value="<?= h($s['price'] ?? '') ?>" placeholder="Price" /></div>
                <div class="col-md-4"><input class="form-control" name="time[<?= $i ?>]" value="<?= h($s['time'] ?? '') ?>" placeholder="Time" /></div>
                <div class="col-md-4"><input class="form-control" name="desc[<?= $i ?>]" value="<?= h($s['desc'] ?? '') ?>" placeholder="Description" /></div>
              </div>
            </div>
          <?php endforeach; ?>
          <div class="col-12"><button class="btn fw-semibold" style="background:#003d82;color:#fff" type="submit">Save</button></div>
        </form>
      </div>

<?php elseif ($tab === 'admins'):
  $rows = db_all("SELECT * FROM users WHERE role IN ('admin','super_admin') ORDER BY role DESC, created_at");
  $adminCount = count($rows);
  $settingsActivity = db_all('SELECT * FROM activity_logs WHERE action LIKE "%settings%" OR action LIKE "%admin%" OR action LIKE "%Admin%" OR action LIKE "%Updated website%" ORDER BY id DESC LIMIT 6');
  if (!$settingsActivity) {
      $settingsActivity = db_all('SELECT * FROM activity_logs ORDER BY id DESC LIMIT 6');
  }
  $notifyOn = ($site['enableNotifications'] ?? '1') === '1';
  $autoSaveOn = ($site['autoSaveRecords'] ?? '1') === '1';
  $announceOn = ($site['publicAnnouncementAlerts'] ?? '1') === '1';
?>
      <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
          <div class="admin-stat-card">
            <div class="stat-label">Admin Profile</div>
            <div class="stat-value" style="font-size:1.15rem;color:#2563eb"><?= h($adminName) ?></div>
            <div class="stat-trend"><?= h($roleText) ?></div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="admin-stat-card">
            <div class="stat-label">User Roles</div>
            <div class="stat-value" style="color:#7c3aed"><?= (int) $adminCount ?></div>
            <div class="stat-trend">Active admin accounts</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="admin-stat-card">
            <div class="stat-label">Security</div>
            <div class="stat-value" style="font-size:1.15rem;color:#16a34a">Email OTP</div>
            <div class="stat-trend">Resident verification enabled</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="admin-stat-card">
            <div class="stat-label">Data Backup</div>
            <div class="stat-value" style="font-size:1.15rem;color:#0891b2">Hostinger</div>
            <div class="stat-trend">Use hPanel backups</div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-xl-8">
          <div class="admin-panel mb-3">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">General Settings</h2>
                <p class="admin-panel-sub">Barangay identity and system preferences</p>
              </div>
            </div>
            <div class="p-3 p-md-4">
              <form method="post" class="row g-3" id="settingsGeneralForm">
                <input type="hidden" name="action" value="save_site" />
                <input type="hidden" name="return_tab" value="admins" />
                <input type="hidden" name="emergencyCall" value="<?= h($site['emergencyCall'] ?? '') ?>" />
                <input type="hidden" name="emergencyLead" value="<?= h($site['emergencyLead'] ?? '') ?>" />
                <input type="hidden" name="officeAddress" value="<?= h($site['officeAddress'] ?? '') ?>" />
                <input type="hidden" name="contactPhones" value="<?= h($site['contactPhones'] ?? '') ?>" />
                <input type="hidden" name="contactEmail" value="<?= h($site['contactEmail'] ?? '') ?>" />
                <input type="hidden" name="officeHours" value="<?= h($site['officeHours'] ?? '') ?>" />
                <input type="hidden" name="footerLocation" value="<?= h($site['footerLocation'] ?? '') ?>" />
                <?php foreach ($site['services'] ?? [] as $i => $s): ?>
                  <input type="hidden" name="price[<?= $i ?>]" value="<?= h($s['price'] ?? '') ?>" />
                  <input type="hidden" name="time[<?= $i ?>]" value="<?= h($s['time'] ?? '') ?>" />
                  <input type="hidden" name="desc[<?= $i ?>]" value="<?= h($s['desc'] ?? '') ?>" />
                <?php endforeach; ?>

                <div class="col-md-4">
                  <label class="form-label fw-semibold small">Barangay Name</label>
                  <input class="form-control" name="barangayName" value="<?= h($site['barangayName'] ?? 'Barangay Sta. Cruz') ?>" />
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold small">Municipality</label>
                  <input class="form-control" name="municipality" value="<?= h($site['municipality'] ?? 'Bacnotan') ?>" />
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold small">Province</label>
                  <input class="form-control" name="province" value="<?= h($site['province'] ?? 'La Union') ?>" />
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold small">System Language</label>
                  <select class="form-select" name="systemLanguage">
                    <?php foreach (['English', 'Filipino'] as $lang): ?>
                      <option <?= ($site['systemLanguage'] ?? 'English') === $lang ? 'selected' : '' ?>><?= $lang ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold small">Time Zone</label>
                  <select class="form-select" name="timeZone">
                    <?php foreach (['Asia/Manila'] as $tz): ?>
                      <option <?= ($site['timeZone'] ?? 'Asia/Manila') === $tz ? 'selected' : '' ?>><?= $tz ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold small">Date Format</label>
                  <select class="form-select" name="dateFormat">
                    <?php foreach (['F j, Y' => 'Month Day, Year', 'Y-m-d' => 'YYYY-MM-DD', 'm/d/Y' => 'MM/DD/YYYY'] as $val => $label): ?>
                      <option value="<?= h($val) ?>" <?= ($site['dateFormat'] ?? 'F j, Y') === $val ? 'selected' : '' ?>><?= h($label) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-12">
                  <div class="rounded-3 p-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                    <div class="form-check form-switch mb-2">
                      <input class="form-check-input" type="checkbox" role="switch" id="enableNotifications" name="enableNotifications" value="1" <?= $notifyOn ? 'checked' : '' ?> />
                      <label class="form-check-label fw-semibold" for="enableNotifications">Enable Notifications</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                      <input class="form-check-input" type="checkbox" role="switch" id="autoSaveRecords" name="autoSaveRecords" value="1" <?= $autoSaveOn ? 'checked' : '' ?> />
                      <label class="form-check-label fw-semibold" for="autoSaveRecords">Auto-save Records</label>
                    </div>
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" role="switch" id="publicAnnouncementAlerts" name="publicAnnouncementAlerts" value="1" <?= $announceOn ? 'checked' : '' ?> />
                      <label class="form-check-label fw-semibold" for="publicAnnouncementAlerts">Public Announcement Alerts</label>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <div class="admin-panel" id="adminForm">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">User Access Management</h2>
                <p class="admin-panel-sub">Admin accounts that can sign in to this panel (max 3)</p>
              </div>
            </div>
            <?php if ($isSuper): ?>
              <div class="p-3 border-bottom">
                <form method="post" class="row g-2 align-items-end">
                  <input type="hidden" name="action" value="save_admin" />
                  <div class="col-md-2"><label class="form-label small fw-semibold mb-1">First name</label><input class="form-control form-control-sm" name="first_name" required /></div>
                  <div class="col-md-2"><label class="form-label small fw-semibold mb-1">Last name</label><input class="form-control form-control-sm" name="last_name" required /></div>
                  <div class="col-md-2"><label class="form-label small fw-semibold mb-1">Mobile</label><input class="form-control form-control-sm" name="phone" required /></div>
                  <div class="col-md-3"><label class="form-label small fw-semibold mb-1">Email</label><input class="form-control form-control-sm" name="email" type="email" /></div>
                  <div class="col-md-2"><label class="form-label small fw-semibold mb-1">Password</label><input class="form-control form-control-sm" name="password" type="password" required minlength="8" /></div>
                  <div class="col-md-1"><button class="btn btn-sm btn-admin-primary w-100" style="padding:.45rem .5rem !important" type="submit"><?= admin_icon('plus', 14) ?></button></div>
                </form>
              </div>
            <?php else: ?>
              <div class="px-3 pt-3"><p class="text-muted small">Only Super Admin can create admin accounts.</p></div>
            <?php endif; ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <?php foreach (['User ID', 'Name', 'Role', 'Status', 'Contact', 'Actions'] as $h): ?>
                      <th><?= h($h) ?></th>
                    <?php endforeach; ?>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($rows as $r):
                    $nm = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
                    $ini = strtoupper(substr($nm !== '' ? $nm : 'A', 0, 1));
                    $isSa = ($r['role'] ?? '') === 'super_admin';
                  ?>
                    <tr>
                      <td class="fw-bold" style="color:#2563eb">ADM-<?= str_pad((string) $r['id'], 3, '0', STR_PAD_LEFT) ?></td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width:32px;height:32px;font-size:.75rem;background:<?= $isSa ? '#d97706' : '#2563eb' ?>"><?= h($ini) ?></div>
                          <div class="fw-semibold" style="font-size:.875rem"><?= h($nm) ?></div>
                        </div>
                      </td>
                      <td>
                        <span class="badge rounded-pill px-2 py-1" style="background:<?= $isSa ? '#fef3c7;color:#92400e' : '#dbeafe;color:#1d4ed8' ?>;font-size:.72rem">
                          <?= h(role_label($r)) ?>
                        </span>
                      </td>
                      <td><span class="badge rounded-pill px-2 py-1" style="background:#dcfce7;color:#166534;font-size:.72rem"><?= h($r['status'] ?? 'active') ?></span></td>
                      <td class="text-muted" style="font-size:.8rem">
                        <div><?= h($r['email'] ?: '—') ?></div>
                        <div><?= h($r['phone'] ?: '') ?></div>
                      </td>
                      <td>
                        <?php if ($isSuper && ($r['role'] ?? '') === 'admin'): ?>
                          <form method="post" class="d-inline" onsubmit="return confirm('Delete this admin account?')">
                            <input type="hidden" name="action" value="delete_admin" />
                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>" />
                            <button class="admin-icon-btn" style="width:32px;height:32px;color:#ef4444" type="submit" title="Delete"><?= admin_icon('trash', 14) ?></button>
                          </form>
                        <?php else: ?>
                          <span class="text-muted" style="font-size:.75rem">—</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-xl-4">
          <div class="admin-panel mb-3">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">Account &amp; Security</h2>
                <p class="admin-panel-sub">Signed-in administrator</p>
              </div>
            </div>
            <div class="p-3 text-center">
              <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold mb-2"
                style="width:64px;height:64px;font-size:1.4rem;background:#2563eb"><?= h($initial) ?></div>
              <div class="fw-bold"><?= h($adminName) ?></div>
              <div class="text-muted small mb-1"><?= h($u['email'] ?? '—') ?></div>
              <div class="mb-3"><span class="badge rounded-pill px-2 py-1" style="background:#eff6ff;color:#1d4ed8"><?= h($roleText) ?></span></div>
              <div class="d-grid gap-2">
                <a class="btn-admin-secondary justify-content-center" href="<?= h(base_url('admin?tab=website')) ?>">Prices &amp; Contacts</a>
                <a class="btn-admin-secondary justify-content-center" href="<?= h(base_url('logout')) ?>">Sign out</a>
              </div>
            </div>
          </div>

          <div class="admin-panel mb-3">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">Backup &amp; Data</h2>
                <p class="admin-panel-sub">Hosting backup tools</p>
              </div>
            </div>
            <div class="p-3">
              <div class="mb-2 d-flex justify-content-between small"><span class="text-muted">Database</span><strong>MySQL · Hostinger</strong></div>
              <div class="progress mb-3" style="height:8px;border-radius:999px">
                <div class="progress-bar" style="width:68%;background:#2563eb"></div>
              </div>
              <p class="text-muted small mb-3">Use Hostinger hPanel → Files / Databases for backups and exports.</p>
              <div class="d-grid gap-2">
                <a class="btn-admin-secondary justify-content-center" href="<?= h(base_url('admin?tab=activity')) ?>"><?= admin_icon('download', 14) ?> View activity export path</a>
                <a class="btn-admin-secondary justify-content-center" href="<?= h(base_url('admin?tab=website')) ?>">Edit public site data</a>
              </div>
            </div>
          </div>

          <div class="admin-panel">
            <div class="admin-panel-head">
              <div>
                <h2 class="admin-panel-title">Recent settings activity</h2>
                <p class="admin-panel-sub">Latest admin changes</p>
              </div>
              <a href="<?= h(base_url('admin?tab=activity')) ?>" style="font-size:.75rem;font-weight:700;color:#2563eb;text-decoration:none">View all</a>
            </div>
            <div class="px-3 pb-2">
              <?php if (!$settingsActivity): ?>
                <p class="text-muted small py-3 mb-0">No activity yet.</p>
              <?php else: foreach ($settingsActivity as $act): ?>
                <div class="admin-activity-item">
                  <div class="admin-activity-dot"></div>
                  <div>
                    <div class="fw-semibold" style="font-size:.84rem"><?= h($act['action'] ?? 'Action') ?></div>
                    <div class="text-muted" style="font-size:.72rem"><?= h($act['detail'] ?? '') ?></div>
                    <div class="text-muted" style="font-size:.68rem"><?= h($act['created_at'] ?? '') ?></div>
                  </div>
                </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>
      </div>

<?php elseif ($tab === 'messages'):
  $rows = db_all('SELECT * FROM messages ORDER BY created_at DESC LIMIT 80');
?>
      <div class="admin-msg-compose card-soft mb-3">
        <h3 class="h6 fw-bold mb-2" style="color:#0f172a">Reply to residents</h3>
        <p class="text-muted small mb-3">Posts a public reply visible on the resident dashboard.</p>
        <form method="post" class="row g-3">
          <input type="hidden" name="action" value="reply_message" />
          <div class="col-12"><textarea class="form-control" name="message" required placeholder="Type your reply…" rows="3"></textarea></div>
          <div class="col-12"><button class="btn-admin-primary" type="submit">Send reply</button></div>
        </form>
      </div>

      <div class="admin-messages">
        <?php if (!$rows): ?>
          <div class="card-soft"><p class="text-muted mb-0">No messages yet.</p></div>
        <?php endif; ?>
        <?php foreach ($rows as $m):
          $role = (string) ($m['sender_role'] ?? '');
          $contact = ($role === 'guest') ? parse_contact_message((string) ($m['message'] ?? '')) : null;
          $roleLabel = match ($role) {
            'guest' => 'Website contact',
            'admin', 'super_admin' => 'Admin reply',
            'resident' => 'Resident',
            default => $role !== '' ? ucfirst($role) : 'Message',
          };
        ?>
          <article class="admin-msg-card">
            <header class="admin-msg-card-head">
              <div>
                <div class="admin-msg-name"><?= h($m['sender_name'] ?: 'Unknown') ?></div>
                <div class="admin-msg-meta">
                  <span class="admin-msg-badge"><?= h($roleLabel) ?></span>
                  <span><?= h($m['created_at']) ?></span>
                </div>
              </div>
              <?php if ($contact && $contact['email'] !== ''): ?>
                <a class="admin-msg-mail" href="mailto:<?= h($contact['email']) ?>?subject=Re:%20<?= h(rawurlencode($contact['subject'])) ?>"><?= h($contact['email']) ?></a>
              <?php endif; ?>
            </header>
            <?php if ($contact): ?>
              <div class="admin-msg-subject"><?= h($contact['subject']) ?></div>
              <p class="admin-msg-body"><?= nl2br(h($contact['body'])) ?></p>
              <form method="post" class="admin-msg-reply">
                <input type="hidden" name="action" value="reply_contact" />
                <input type="hidden" name="message_id" value="<?= (int) $m['id'] ?>" />
                <input type="hidden" name="email" value="<?= h($contact['email']) ?>" />
                <input type="hidden" name="subject" value="<?= h($contact['subject']) ?>" />
                <input type="hidden" name="name" value="<?= h($m['sender_name'] ?? '') ?>" />
                <label class="form-label fw-semibold mb-1" style="font-size:.8rem;color:#475569">Reply by email to <?= h($contact['email']) ?></label>
                <textarea class="form-control" name="message" rows="3" required placeholder="Write your reply to this complaint / inquiry…"></textarea>
                <button class="btn-admin-primary mt-2" type="submit">Send email reply</button>
              </form>
            <?php else: ?>
              <p class="admin-msg-body mb-0"><?= nl2br(h((string) ($m['message'] ?? ''))) ?></p>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
<?php endif; ?>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= h(base_url('assets/govly2.js')) ?>?v=8"></script>
<script>
(function () {
  var sidebar = document.getElementById('sidebar');
  var toggle = document.getElementById('sidebarToggle');
  var backdrop = document.getElementById('sidebarBackdrop');
  function setOpen(open) {
    if (!sidebar) return;
    sidebar.classList.toggle('open', open);
    if (backdrop) {
      backdrop.hidden = !open;
      backdrop.style.display = open ? 'block' : 'none';
    }
  }
  if (toggle) toggle.addEventListener('click', function () { setOpen(!sidebar.classList.contains('open')); });
  if (backdrop) backdrop.addEventListener('click', function () { setOpen(false); });

  var themeBtn = document.getElementById('adminThemeToggle');
  function applyTheme(mode) {
    document.documentElement.setAttribute('data-theme', mode === 'dark' ? 'dark' : 'light');
    try { localStorage.setItem('govly-theme', mode); } catch (e) {}
    if (themeBtn) {
      themeBtn.innerHTML = mode === 'dark'
        ? <?= json_encode(admin_icon('sun', 16)) ?>
        : <?= json_encode(admin_icon('moon', 16)) ?>;
      themeBtn.title = mode === 'dark' ? 'Light mode' : 'Night mode';
    }
  }
  var saved = 'light';
  try { saved = localStorage.getItem('govly-theme') || 'light'; } catch (e) {}
  applyTheme(saved);
  if (themeBtn) themeBtn.addEventListener('click', function () {
    var cur = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    applyTheme(cur === 'dark' ? 'light' : 'dark');
  });
})();
</script>
</body></html>
