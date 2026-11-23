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
  <link rel="stylesheet" href="<?= h(base_url('assets/finale.css')) ?>?v=7" />
  <link rel="stylesheet" href="<?= h(base_url('assets/govly2.css')) ?>?v=7" />
</head>
<body>
<?php if ($msg = flash('success')): ?><div class="flash ok"><?= h($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="flash err"><?= h($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('info')): ?><div class="flash info"><?= h($msg) ?></div><?php endif; ?>
