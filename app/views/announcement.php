<?php
ensure_announcements_schema();
$id = (int) ($_GET['id'] ?? 0);
$row = $id > 0 ? db_one('SELECT * FROM announcements WHERE id = ?', [$id]) : null;
if (!$row) {
    flash('error', 'Announcement not found.');
    redirect('#announcements');
}
$gallery = announcement_gallery($row);
$cover = trim((string) ($row['cover_path'] ?? ''));
$dateLabel = !empty($row['created_at']) ? date('F j, Y', strtotime((string) $row['created_at'])) : '';
$title = (string) $row['title'] . ' — Barangay Sta. Cruz';
$more = db_all(
    'SELECT * FROM announcements WHERE id <> ? ORDER BY featured DESC, created_at DESC LIMIT 4',
    [$id]
);
require __DIR__ . '/_head.php';
require __DIR__ . '/_nav.php';
?>
<main class="announcement-page">
  <div class="container-xl px-3 py-4 py-lg-5">
    <a class="announcement-back" href="<?= h(base_url('#announcements')) ?>">← Back to News &amp; Announcements</a>
    <header class="announcement-hero">
      <?php if (!empty($row['featured'])): ?>
        <span class="pill pill-orange mb-2">Featured</span>
      <?php else: ?>
        <span class="pill pill-red mb-2">Latest News</span>
      <?php endif; ?>
      <h1 class="announcement-title"><?= h($row['title']) ?></h1>
      <?php if ($dateLabel !== ''): ?>
        <p class="announcement-date"><?= h($dateLabel) ?></p>
      <?php endif; ?>
    </header>

    <div class="announcement-layout">
      <div class="announcement-media">
        <?php if ($cover !== ''): ?>
          <img src="<?= h(base_url($cover)) ?>" alt="<?= h($row['title']) ?>" />
        <?php else: ?>
          <div class="news-card-fallback announcement-fallback">
            <span>Barangay Sta. Cruz</span>
          </div>
        <?php endif; ?>
      </div>
      <div class="announcement-copy">
        <?= nl2br(h((string) ($row['body'] ?? ''))) ?>
      </div>
    </div>

    <?php if ($gallery): ?>
      <section class="announcement-gallery">
        <h2>Photo gallery</h2>
        <div class="announcement-gallery-grid">
          <?php foreach ($gallery as $path): ?>
            <a href="<?= h(base_url($path)) ?>" target="_blank" rel="noopener">
              <img src="<?= h(base_url($path)) ?>" alt="" loading="lazy" />
            </a>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <?php if ($more): ?>
      <section class="announcement-more">
        <h2>More announcements</h2>
        <div class="row g-3">
          <?php foreach ($more as $a):
            $aCover = trim((string) ($a['cover_path'] ?? ''));
          ?>
            <div class="col-md-6 col-lg-3">
              <a class="announcement-more-card" href="<?= h(base_url('announcement/' . (int) $a['id'])) ?>">
                <?php if ($aCover !== ''): ?>
                  <img src="<?= h(base_url($aCover)) ?>" alt="" loading="lazy" />
                <?php else: ?>
                  <div class="news-card-fallback" style="min-height:120px"><span>News</span></div>
                <?php endif; ?>
                <strong><?= h($a['title']) ?></strong>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
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
