<?php
$title = 'Emergency Services · Barangay Sta. Cruz';
$site = site();
$dispatch = $site['dispatch'] ?? [];
$related = $site['related'] ?? [];
$hotlines = [
    ['911', 'tel:911', 'La Union Hotline', 'Telephone'],
    ['0919-546-1800', 'tel:09195461800', 'Bacnotan Rescue', 'Smart'],
    ['0949-474-2161', 'tel:09494742161', 'MDRRMO', 'Smart'],
    ['0998-598-5156', 'tel:09985985156', 'PNP – Bacnotan', 'Smart'],
    ['0917-184-2711', 'tel:09171842711', 'BFP – Bacnotan', 'Globe'],
];
$evacMap = 'https://www.google.com/maps/d/embed?mid=1ojLIKdvBAFRfbtzfSw1o747gd3FyjZ8&ehbc=2E312F&noprof=1';
require __DIR__ . '/_head.php';
require __DIR__ . '/_nav.php';
?>
<main class="emergency-page">
  <div class="container-xl px-3 py-4 py-lg-5">
    <a class="announcement-back" href="<?= h(base_url('#emergency-services')) ?>">← Back to Emergency Hotlines</a>
    <div class="section-intro text-center mb-4">
      <span class="pill pill-blue">Public safety</span>
      <h1 class="bac-heading">Emergency Services</h1>
      <p class="bac-lead"><?= h($site['emergencyLead'] ?? 'Evacuation centers across Bacnotan and the main municipal hotlines. Call 911 first when life, fire, or crime is involved.') ?></p>
    </div>

    <div class="row g-4 align-items-stretch mb-5">
      <div class="col-lg-7">
        <div class="bac-map-frame">
          <iframe class="bac-map" title="Bacnotan evacuation centers" src="<?= h($evacMap) ?>" loading="lazy" allowfullscreen></iframe>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="bac-hotlines">
          <?php foreach ($hotlines as [$num, $href, $name, $net]): ?>
            <a class="bac-hotline" href="<?= h($href) ?>">
              <span class="bac-hotline-num"><?= h($num) ?></span>
              <span class="bac-hotline-name"><?= h($name) ?></span>
              <span class="bac-hotline-net"><?= h($net) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <h2 class="h4 mb-3" style="color:#003d82">Primary dispatch</h2>
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

    <h2 class="h4 mb-3" style="color:#003d82">Related contacts</h2>
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
</main>
<footer class="text-white pt-4 pb-4" style="background:linear-gradient(135deg,#0038A8,#001a47)">
  <div class="container-xl px-3 text-center">
    <p class="mb-0 small opacity-75">© <?= date('Y') ?> Barangay Sta. Cruz, Bacnotan, La Union</p>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= h(base_url('assets/govly2.js')) ?>?v=8"></script>
</body></html>
