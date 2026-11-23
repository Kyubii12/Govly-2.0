<?php
$title = 'Emergency Hotlines · GovLy 2.0';
$site = site();
$dispatch = $site['dispatch'] ?? [];
$related = $site['related'] ?? [];
require __DIR__ . '/_head.php';
require __DIR__ . '/_nav.php';
?>
<div class="container-xl px-3 py-4">
  <div class="section-intro text-center mb-4">
    <span class="pill pill-orange">Emergency</span>
    <h1 style="color:#003d82;font-weight:800">Emergency Hotlines</h1>
    <p class="text-muted"><?= h($site['emergencyLead'] ?? '') ?></p>
  </div>

  <h2 class="h4" style="color:#003d82">Primary dispatch</h2>
  <div class="row g-3 mb-4">
    <?php foreach ($dispatch as $row): ?>
      <div class="col-12 col-md-6 col-lg-4">
        <article class="card-soft hotline-card h-100">
          <h3 class="h5 fw-bold" style="color:#003d82"><?= h($row['name'] ?? '') ?></h3>
          <p class="text-muted small"><?= h($row['purpose'] ?? '') ?></p>
          <?php foreach ($row['phones'] ?? [] as $ph): ?>
            <a class="hotline-num" href="<?= h(tel_href($ph)) ?>"><?= h($ph) ?></a>
          <?php endforeach; ?>
          <?php if (!empty($row['note'])): ?><p class="text-muted small mb-0"><?= h($row['note']) ?></p><?php endif; ?>
        </article>
      </div>
    <?php endforeach; ?>
  </div>

  <h2 class="h4" style="color:#003d82">Related contacts</h2>
  <div class="row g-3">
    <?php foreach ($related as $row): ?>
      <div class="col-12 col-md-6 col-lg-4">
        <article class="card-soft hotline-card h-100">
          <h3 class="h5 fw-bold" style="color:#003d82"><?= h($row['name'] ?? '') ?></h3>
          <p class="text-muted small"><?= h($row['kind'] ?? '') ?></p>
          <?php foreach ($row['phones'] ?? [] as $ph): ?>
            <a class="hotline-num" href="<?= h(tel_href($ph)) ?>"><?= h($ph) ?></a>
          <?php endforeach; ?>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= h(base_url('assets/govly2.js')) ?>"></script>
</body></html>
