<?php
$u = current_user();
$title = $title ?? ($GLOBALS['CONFIG']['app_name'] ?? 'GovLy 2.0');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= h($title) ?></title>
  <link rel="icon" href="<?= h(base_url('assets/sta-cruz.jpg')) ?>" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <meta name="theme-color" content="#0038A8" />
  <link rel="stylesheet" href="<?= h(base_url('assets/leaflet/leaflet.css')) ?>?v=7" />
  <link rel="stylesheet" href="<?= h(base_url('assets/finale.css')) ?>?v=15" />
  <link rel="stylesheet" href="<?= h(base_url('assets/govly2.css')) ?>?v=16" />
  <?php if (!empty($adminLayout)): ?>
  <link rel="stylesheet" href="<?= h(base_url('assets/admin-ui.css')) ?>?v=20" />
  <style>
    /* Admin must scroll with the page — never lock html/body to the viewport */
    html:has(body.admin-page) {
      height: auto !important;
      overflow-x: hidden !important;
      overflow-y: scroll !important;
    }
    body.admin-page {
      margin: 0;
      background: #eef3f9;
      height: auto !important;
      min-height: 100dvh !important;
      overflow-x: hidden !important;
      overflow-y: visible !important;
    }
    body.admin-page .flash {
      position: fixed; top: 12px; right: 12px; z-index: 2000;
      max-width: min(420px, calc(100vw - 24px));
    }
    body.admin-page .admin-shell {
      display: block !important;
      min-height: 100dvh !important;
      height: auto !important;
      overflow: visible !important;
      background: #eef3f9;
    }
    body.admin-page .admin-content {
      margin-left: 268px;
      min-height: 100dvh;
      height: auto !important;
      overflow: visible !important;
      background: #eef3f9;
      padding-bottom: max(2rem, env(safe-area-inset-bottom));
    }
    body.admin-page .admin-content > .p-4 {
      padding-bottom: 2.5rem !important;
    }
    body.admin-page .site-nav,
    body.admin-page .nav-spacer { display: none !important; }
    body.admin-page #sidebar {
      position: fixed;
      top: 0; left: 0;
      width: 268px;
      height: 100dvh;
      max-height: 100dvh;
      min-height: 0 !important;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }
    body.admin-page #sidebar .sidebar-nav {
      flex: 1 1 auto;
      min-height: 0;
      overflow-y: auto;
      -webkit-overflow-scrolling: touch;
    }
    body.admin-page #sidebar a.sidebar-link { text-decoration: none; }
    body.admin-page .admin-topbar { position: sticky; top: 0; z-index: 1020; }
    /* Dashboard / tables stay usable on short and narrow screens */
    body.admin-page .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    body.admin-page .census-age-chart { max-height: none; overflow: visible; }
    @media (max-width: 991.98px) {
      body.admin-page .admin-content { margin-left: 0; }
      body.admin-page #sidebar {
        transform: translateX(-100%);
      }
      body.admin-page #sidebar.open {
        transform: translateX(0);
      }
    }
    @media print {
      body.admin-page #sidebar,
      body.admin-page .admin-topbar,
      body.admin-page .sidebar-backdrop { display: none !important; }
      body.admin-page .admin-content { margin-left: 0 !important; }
      html:has(body.admin-page) { overflow: visible !important; }
    }
  </style>
  <?php endif; ?>
</head>
<body<?= !empty($adminLayout) ? ' class="admin-page"' : '' ?>>
<?php if ($msg = flash('success')): ?><div class="flash ok"><?= h($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="flash err"><?= h($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('info')): ?><div class="flash info"><?= h($msg) ?></div><?php endif; ?>
