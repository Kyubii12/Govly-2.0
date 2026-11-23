<?php $u = current_user(); ?>
<nav class="site-nav gradient-bg hero-pattern shadow-lg" id="topNav">
  <div class="container-xl px-3">
    <div class="site-nav-bar d-flex align-items-center justify-content-between">
      <a class="d-flex align-items-center gap-3 text-decoration-none" href="<?= h(base_url()) ?>">
        <img src="<?= h(base_url('assets/sta-cruz.jpg')) ?>" alt="Barangay Sta.Cruz Logo"
          style="width:48px;height:48px;object-fit:cover;border-radius:.5rem;flex-shrink:0;box-shadow:0 4px 6px rgba(0,0,0,.1)" />
        <div class="site-brand">
          <h1 class="site-brand-title fw-bold mb-0" style="line-height:1.2;color:#fff">Barangay Sta.Cruz</h1>
          <p class="mb-0" style="font-size:.7rem;color:#fff">Bacnotan City</p>
        </div>
      </a>

      <div class="d-none d-md-flex align-items-center gap-4">
        <a class="nav-link-custom" href="<?= h(base_url('#home')) ?>">Home</a>
        <a class="nav-link-custom" href="<?= h(base_url('#services')) ?>">Services</a>
        <a class="nav-link-custom" href="<?= h(base_url('#officials')) ?>">Officials</a>
        <a class="nav-link-custom" href="<?= h(base_url('#announcements')) ?>">News</a>
        <a class="nav-link-custom" href="<?= h(base_url('emergency')) ?>">Emergency</a>
        <a class="nav-link-custom" href="<?= h(base_url('#contact')) ?>">Contact</a>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button type="button" id="themeToggle" title="Night mode" aria-label="Night mode">☾</button>
        <div class="dropdown">
          <button class="btn btn-warning fw-bold dropdown-toggle rounded-3 d-flex align-items-center gap-2 shadow-sm" type="button" data-bs-toggle="dropdown" style="font-size:.875rem">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span><?= $u ? h($u['first_name']) : 'Account' ?></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow rounded-3">
            <?php if (!$u): ?>
              <li><a class="dropdown-item fw-semibold" href="<?= h(base_url('login')) ?>">Login</a></li>
              <li><a class="dropdown-item fw-semibold" href="<?= h(base_url('register')) ?>">Register</a></li>
            <?php else: ?>
              <li><h6 class="dropdown-header"><?= h(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''))) ?> · <?= h($u['role']) ?></h6></li>
              <?php if (is_admin_user($u)): ?>
                <li><a class="dropdown-item" href="<?= h(base_url('admin')) ?>">Admin dashboard</a></li>
              <?php else: ?>
                <li><a class="dropdown-item" href="<?= h(base_url('dashboard')) ?>">My requests</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= h(base_url('logout')) ?>">Logout</a></li>
            <?php endif; ?>
          </ul>
        </div>
        <button class="btn text-white d-md-none p-2 rounded-2" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav" aria-label="Menu">☰</button>
      </div>
    </div>
    <div class="collapse d-md-none pb-3" id="mobileNav">
      <div class="d-flex flex-column gap-1 px-1">
        <a class="nav-link-custom py-2" href="<?= h(base_url('#home')) ?>">Home</a>
        <a class="nav-link-custom py-2" href="<?= h(base_url('#services')) ?>">Services</a>
        <a class="nav-link-custom py-2" href="<?= h(base_url('#officials')) ?>">Officials</a>
        <a class="nav-link-custom py-2" href="<?= h(base_url('#announcements')) ?>">News</a>
        <a class="nav-link-custom py-2" href="<?= h(base_url('emergency')) ?>">Emergency</a>
        <a class="nav-link-custom py-2" href="<?= h(base_url('#contact')) ?>">Contact</a>
      </div>
    </div>
  </div>
</nav>
<div class="nav-spacer"></div>
