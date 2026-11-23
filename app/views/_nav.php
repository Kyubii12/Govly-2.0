<?php $u = current_user(); ?>
<nav class="site-nav gradient-bg hero-pattern shadow-lg" id="topNav"
  style="position:fixed;top:0;left:0;right:0;z-index:50;background:linear-gradient(135deg,#0038A8 0%,#002570 50%,#001a47 100%)">
  <div class="container-xl px-3">
    <div class="site-nav-bar d-flex align-items-center justify-content-between">
      <a class="d-flex align-items-center gap-3 text-decoration-none" href="<?= h(base_url()) ?>" style="cursor:pointer">
        <img src="<?= h(base_url('assets/sta-cruz.jpg')) ?>" alt="Barangay Sta.Cruz Logo"
          style="width:48px;height:48px;object-fit:cover;border-radius:.5rem;flex-shrink:0;box-shadow:0 4px 6px rgba(0,0,0,.1)" />
        <div class="site-brand">
          <h1 class="site-brand-title fw-bold mb-0" style="line-height:1.2;color:#fff">Barangay Sta.Cruz</h1>
          <p class="mb-0" style="font-size:.7rem;color:#fff">Bacnotan City</p>
        </div>
      </a>

      <div class="d-none d-md-flex align-items-center gap-4">
        <a class="nav-link-custom" href="<?= h(base_url('#home')) ?>" style="font-size:.9rem;color:#fff;font-weight:500">Home</a>
        <a class="nav-link-custom" href="<?= h(base_url('#services')) ?>" style="font-size:.9rem;color:#fff;font-weight:500">Services</a>
        <a class="nav-link-custom" href="<?= h(base_url('#officials')) ?>" style="font-size:.9rem;color:#fff;font-weight:500">Officials</a>
        <a class="nav-link-custom" href="<?= h(base_url('#announcements')) ?>" style="font-size:.9rem;color:#fff;font-weight:500">News</a>
        <a class="nav-link-custom" href="<?= h(base_url('emergency')) ?>" style="font-size:.9rem;color:#fff;font-weight:500">Emergency</a>
        <a class="nav-link-custom" href="<?= h(base_url('#contact')) ?>" style="font-size:.9rem;color:#fff;font-weight:500">Contact</a>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button type="button" id="themeToggle" class="btn btn-sm text-white" title="Night mode" aria-label="Night mode"
          style="width:38px;height:38px;padding:0;border-radius:10px;border:1px solid rgba(255,255,255,.45);background:rgba(255,255,255,.08)">☾</button>
        <div class="dropdown d-none d-md-block">
          <button class="btn btn-warning fw-bold d-flex align-items-center gap-2 shadow-sm rounded-3" type="button" data-bs-toggle="dropdown" style="font-size:.875rem">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span><?= $u ? h($u['first_name']) : 'Account' ?></span>
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 border" style="width:215px;z-index:9999">
            <?php if (!$u): ?>
              <li class="px-2 pt-2"><a class="dropdown-item rounded-2 fw-semibold" href="<?= h(base_url('login')) ?>">Login</a></li>
              <li class="px-2 pb-2"><a class="dropdown-item rounded-2 fw-semibold btn-warning" href="<?= h(base_url('register')) ?>" style="background:#ffc107">Register</a></li>
            <?php else: ?>
              <li><h6 class="dropdown-header" style="background:#fffbeb"><?= h(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''))) ?> · <?= h($u['role']) ?></h6></li>
              <?php if (is_admin_user($u)): ?>
                <li><a class="dropdown-item" href="<?= h(base_url('admin')) ?>">Admin Panel</a></li>
              <?php else: ?>
                <li><a class="dropdown-item" href="<?= h(base_url('dashboard')) ?>">Dashboard</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= h(base_url('logout')) ?>">Logout</a></li>
            <?php endif; ?>
          </ul>
        </div>
        <button class="btn d-md-none text-white p-2 rounded-2" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav" aria-label="Menu">☰</button>
      </div>
    </div>
    <div class="collapse d-md-none" id="mobileNav" style="background:rgba(13,27,42,.97);border-top:1px solid rgba(255,255,255,.1)">
      <div class="px-3 py-3 d-flex flex-column gap-1">
        <a class="btn text-start fw-medium py-2 px-3 rounded-2 text-white" href="<?= h(base_url('#home')) ?>">Home</a>
        <a class="btn text-start fw-medium py-2 px-3 rounded-2 text-white" href="<?= h(base_url('#services')) ?>">Services</a>
        <a class="btn text-start fw-medium py-2 px-3 rounded-2 text-white" href="<?= h(base_url('#officials')) ?>">Officials</a>
        <a class="btn text-start fw-medium py-2 px-3 rounded-2 text-white" href="<?= h(base_url('#announcements')) ?>">News</a>
        <a class="btn text-start fw-medium py-2 px-3 rounded-2 text-white" href="<?= h(base_url('emergency')) ?>">Emergency</a>
        <a class="btn text-start fw-medium py-2 px-3 rounded-2 text-white" href="<?= h(base_url('#contact')) ?>">Contact</a>
      </div>
    </div>
  </div>
</nav>
