<?php $u = current_user(); ?>
<nav class="site-nav shadow-lg" id="topNav">
  <div class="container-xl px-3">
    <div class="site-nav-bar d-flex align-items-center justify-content-between">
      <a class="d-flex align-items-center gap-3 text-decoration-none" href="<?= h(base_url()) ?>">
        <img src="<?= h(base_url('assets/sta-cruz.jpg')) ?>" alt="Barangay Sta. Cruz"
          style="width:48px;height:48px;object-fit:cover;border-radius:.5rem;box-shadow:0 4px 6px rgba(0,0,0,.1)" />
        <div class="site-brand">
          <h1 class="site-brand-title fw-bold mb-0" style="line-height:1.2;color:#fff;font-size:1.15rem">Barangay Sta.Cruz</h1>
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
        <button type="button" class="btn btn-sm btn-outline-light rounded-3" id="themeToggle" title="Night mode">☾</button>
        <div class="dropdown">
          <button class="btn btn-warning fw-bold dropdown-toggle rounded-3" type="button" data-bs-toggle="dropdown">
            <?= $u ? h($u['first_name']) : 'Account' ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <?php if (!$u): ?>
              <li><a class="dropdown-item" href="<?= h(base_url('login')) ?>">Login</a></li>
              <li><a class="dropdown-item" href="<?= h(base_url('register')) ?>">Register</a></li>
            <?php else: ?>
              <li><h6 class="dropdown-header"><?= h(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''))) ?> · <?= h($u['role']) ?></h6></li>
              <?php if (is_admin_user($u)): ?>
                <li><a class="dropdown-item" href="<?= h(base_url('admin')) ?>">Admin dashboard</a></li>
              <?php else: ?>
                <li><a class="dropdown-item" href="<?= h(base_url('dashboard')) ?>">My requests</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= h(base_url('logout')) ?>">Logout</a></li>
            <?php endif; ?>
          </ul>
        </div>
        <button class="btn btn-outline-light d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav">☰</button>
      </div>
    </div>
    <div class="collapse d-md-none pb-3" id="mobileNav">
      <div class="d-flex flex-column gap-2">
        <a class="nav-link-custom" href="<?= h(base_url('#services')) ?>">Services</a>
        <a class="nav-link-custom" href="<?= h(base_url('#officials')) ?>">Officials</a>
        <a class="nav-link-custom" href="<?= h(base_url('#announcements')) ?>">News</a>
        <a class="nav-link-custom" href="<?= h(base_url('emergency')) ?>">Emergency</a>
        <a class="nav-link-custom" href="<?= h(base_url('#contact')) ?>">Contact</a>
      </div>
    </div>
  </div>
</nav>
<div class="nav-spacer"></div>
