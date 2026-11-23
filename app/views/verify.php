<?php
$id = strtoupper(trim((string) ($_GET['id'] ?? '')));
$cert = $id ? db_one('SELECT * FROM printed_certs WHERE id = ?', [$id]) : null;
$title = 'Certificate verification — GovLy';
require __DIR__ . '/_head.php';
require __DIR__ . '/_nav.php';
?>
<div class="auth-page">
  <div class="verify-card" style="width:min(560px,100%)">
    <div class="verify-logos">
      <img src="<?= h(base_url('assets/bacnotan-logo.png.png')) ?>" alt="" />
      <img src="<?= h(base_url('assets/bagong-pilipinas-logo.png.png')) ?>" alt="" />
    </div>
    <p class="muted">Barangay Sta. Cruz · Bacnotan, La Union</p>
    <h1>Certificate verification</h1>
    <?php if ($cert): ?>
      <div class="verify-badge is-valid">Valid official certificate</div>
      <dl class="verify-rows">
        <?php foreach ([
          'Control No.' => $cert['id'],
          'Certificate' => $cert['type'],
          'Issued to' => $cert['recipient_name'],
          'Purpose' => $cert['purpose'],
          'Date issued' => $cert['issued_date'],
          'Receipt No.' => $cert['receipt_no'],
          'Certified by' => $cert['certified_by'] ?: 'Punong Barangay',
        ] as $k => $v): ?>
          <div class="verify-row"><dt><?= h($k) ?></dt><dd><?= h($v ?: '—') ?></dd></div>
        <?php endforeach; ?>
      </dl>
      <p class="muted">This record was saved when the barangay printed the certificate.</p>
    <?php else: ?>
      <div class="verify-badge is-invalid">No matching certificate</div>
      <p class="muted">Control No. <?= h($id ?: '—') ?> is not in the official printed-certificate records.</p>
    <?php endif; ?>
    <a class="btn btn-outline" href="<?= h(base_url()) ?>">Back to website</a>
  </div>
</div>
</body></html>
