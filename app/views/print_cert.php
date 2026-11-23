<?php
require_admin();
$id = (int) ($_GET['id'] ?? 0);
$req = db_one('SELECT * FROM requests WHERE id = ?', [$id]);
if (!$req) {
    flash('error', 'Request not found.');
    redirect('admin?tab=requests');
}
$site = site();
$last = $req['last_cert'] ? json_decode($req['last_cert'], true) : [];
$type = $last['type'] ?? $req['certificate_type'];
$tpl = db_one('SELECT * FROM cert_templates WHERE type = ?', [$type]) ?: ['body' => '', 'purpose' => '', 'details' => '', 'closing' => ''];
$name = $last['recipientName'] ?? $req['resident_name'];
$address = 'Barangay Sta. Cruz, Bacnotan, La Union';
$captain = db_one("SELECT name FROM officials WHERE position = 'Punong Barangay' LIMIT 1");
$control = $last['controlNo'] ?? make_control_no();
$body = $last['body'] ?? fill_placeholders($tpl['body'] ?? '', $name, $address);
$purpose = $last['purpose'] ?? ($req['purpose'] ?: ($tpl['purpose'] ?? ''));
$details = $last['details'] ?? ($tpl['details'] ?? '');
$closing = $last['closing'] ?? ($tpl['closing'] ?? '');
$issued = $last['issuedDate'] ?? issued_today();
$certified = $last['certifiedBy'] ?? ($captain['name'] ?? '');
$receipt = 'OR-' . $control;
$amount = service_price($site, $type);
$verify = base_url('verify/' . $control);
$qr = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . urlencode($verify);
$title = 'Print certificate — GovLy';
require __DIR__ . '/_head.php';
?>
<?php if (!empty($_GET['ok'])): ?><div class="flash ok no-print">Saved. Use Print now, then close this tab.</div><?php endif; ?>
<div class="no-print wrap">
  <form method="post" class="card form">
    <input type="hidden" name="action" value="print_cert" />
    <input type="hidden" name="id" value="<?= (int) $id ?>" />
    <input type="hidden" name="control_no" value="<?= h($control) ?>" />
    <input type="hidden" name="receipt_no" value="<?= h($receipt) ?>" />
    <input type="hidden" name="amount" value="<?= h($amount) ?>" />
    <label>Type <input name="type" value="<?= h($type) ?>" /></label>
    <label>Name <input name="recipient_name" value="<?= h($name) ?>" /></label>
    <label>Body <textarea name="body" rows="4"><?= h($body) ?></textarea></label>
    <label>Purpose <input name="purpose" value="<?= h($purpose) ?>" /></label>
    <label>Details <textarea name="details" rows="2"><?= h($details) ?></textarea></label>
    <label>Closing <textarea name="closing" rows="2"><?= h($closing) ?></textarea></label>
    <label>Issued <input name="issued_date" value="<?= h($issued) ?>" /></label>
    <label>Certified by <input name="certified_by" value="<?= h($certified) ?>" /></label>
    <label><input type="checkbox" name="mark_completed" value="1" checked /> Mark completed &amp; email resident</label>
    <button class="btn btn-amber" type="submit">Save for QR then print</button>
    <button class="btn btn-navy" type="button" onclick="window.print()">Print now</button>
    <a class="btn btn-outline" href="<?= h(base_url('admin?tab=requests')) ?>">Back</a>
  </form>
</div>

<div class="cert-sheet">
  <div class="cert-logos">
    <img src="<?= h(base_url('assets/bacnotan-logo.png.png')) ?>" alt="" />
    <img src="<?= h(base_url('assets/bagong-pilipinas-logo.png.png')) ?>" alt="" />
  </div>
  <div class="cert-header">
    <p class="cert-rep">REPUBLIC OF THE PHILIPPINES</p>
    <p>Province of La Union</p>
    <p>Municipality of Bacnotan</p>
    <p class="cert-brgy">BARANGAY STA. CRUZ</p>
    <p class="cert-office">OFFICE OF THE PUNONG BARANGAY</p>
  </div>
  <h2 class="cert-doc-title"><?= h($type) ?></h2>
  <p class="cert-control">Control No. <?= h($control) ?></p>
  <p class="cert-salutation">TO WHOM IT MAY CONCERN:</p>
  <p class="cert-body"><?= nl2br(h($body)) ?></p>
  <?php if ($purpose): ?><p class="cert-purpose">Purpose: <?= h($purpose) ?></p><?php endif; ?>
  <?php if ($details): ?><p class="cert-body"><?= nl2br(h($details)) ?></p><?php endif; ?>
  <p class="cert-body"><?= nl2br(h($closing)) ?></p>
  <p class="cert-issued">Issued this <strong><?= h($issued) ?></strong> at Bacnotan, La Union.</p>
  <div class="cert-sign-row">
    <div class="cert-sign">
      <p class="cert-sign-label">Certified by:</p>
      <p class="cert-sign-name"><?= h($certified) ?></p>
      <p class="cert-sign-role">Punong Barangay</p>
    </div>
    <div class="cert-dry-seal">SEAL</div>
  </div>
  <div class="cert-bottom">
    <div class="cert-official-receipt">
      <p class="cert-or-title">Official Receipt</p>
      <p><span>Control No.</span> <?= h($control) ?></p>
      <p><span>Receipt No.</span> <?= h($receipt) ?></p>
      <p><span>Amount</span> <?= h($amount) ?></p>
      <p><span>Date</span> <?= h(date('F j, Y')) ?></p>
    </div>
    <div class="cert-qr">
      <img src="<?= h($qr) ?>" alt="Verify QR" />
      <span>Scan to verify</span>
    </div>
  </div>
</div>
</body></html>
