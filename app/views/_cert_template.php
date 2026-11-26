<?php
/**
 * Capstone Finale CertificateTemplate — same layout for every certificate type.
 *
 * Expected vars:
 *   $cert (array): type, body, purpose, details, closing, issuedDate, certifiedBy,
 *                  controlNo, receiptNo, receiptPrice, receiptDate, recipientName, address
 *   $certEditable (bool): dashed edit outlines + contenteditable (admin preview)
 *   $certIds (bool): add id=pv* hooks for live preview JS
 */
$cert = $cert ?? [];
$certEditable = !empty($certEditable);
$certIds = !empty($certIds);
$type = (string) ($cert['type'] ?? 'Certificate');
$body = (string) ($cert['body'] ?? '');
$purpose = (string) ($cert['purpose'] ?? '');
$details = (string) ($cert['details'] ?? '');
$closing = (string) ($cert['closing'] ?? 'This certification is issued upon the request of the party concerned for intents and purposes it may serve.');
$issuedDate = (string) ($cert['issuedDate'] ?? issued_today());
$certifiedBy = (string) ($cert['certifiedBy'] ?? '');
$controlNo = (string) ($cert['controlNo'] ?? '');
$receiptNo = (string) ($cert['receiptNo'] ?? '');
$receiptPrice = (string) ($cert['receiptPrice'] ?? '—');
$receiptDate = (string) ($cert['receiptDate'] ?? date('F j, Y'));
$recipientName = (string) ($cert['recipientName'] ?? 'RESIDENT NAME');
$address = (string) ($cert['address'] ?? 'Barangay Sta. Cruz, Bacnotan, La Union');
if ($body === '') {
    $body = "This is to certify that {$recipientName}, of legal age, is a bona fide resident of {$address}.";
}
$editAttr = $certEditable ? ' contenteditable="true"' : '';
$editClass = $certEditable ? ' cert-editable' : '';
$qr = '';
if ($controlNo !== '') {
    $verify = base_url('verify/' . rawurlencode($controlNo));
    $qr = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($verify);
}
$id = static function (string $name) use ($certIds): string {
    return $certIds ? ' id="pv' . ucfirst($name) . '"' : '';
};
?>
<div class="cert-sheet"<?= $certIds ? ' id="certPreviewSheet"' : '' ?>>
  <div class="cert-logos">
    <img src="<?= h(base_url('assets/bacnotan-logo.png.png')) ?>" alt="Barangay Sta. Cruz seal" />
    <img src="<?= h(base_url('assets/bagong-pilipinas-logo.png.png')) ?>" alt="Bagong Pilipinas" />
  </div>

  <div class="cert-header">
    <p class="cert-rep">REPUBLIC OF THE PHILIPPINES</p>
    <p>Province of La Union</p>
    <p>Municipality of Bacnotan</p>
    <p class="cert-brgy">BARANGAY STA. CRUZ</p>
    <p class="cert-office">OFFICE OF THE PUNONG BARANGAY</p>
  </div>

  <h2 class="cert-doc-title<?= h($editClass) ?>" data-cert-field="type"<?= $editAttr ?><?= $id('type') ?>><?= h($type) ?></h2>
  <?php if ($controlNo !== ''): ?>
    <p class="cert-control">Control No. <?= h($controlNo) ?></p>
  <?php endif; ?>

  <p class="cert-salutation">TO WHOM IT MAY CONCERN:</p>

  <p class="cert-body<?= h($editClass) ?>" data-cert-field="body"<?= $editAttr ?><?= $id('body') ?>><?= h($body) ?></p>

  <?php if ($purpose !== '' || $certEditable): ?>
    <p class="cert-purpose">Purpose:
      <span class="<?= h(trim($editClass)) ?>" data-cert-field="purpose"<?= $editAttr ?><?= $id('purpose') ?>><?= h($purpose) ?></span>
    </p>
  <?php endif; ?>

  <?php if ($details !== '' || $certEditable): ?>
    <p class="cert-body<?= h($editClass) ?>" data-cert-field="details"<?= $editAttr ?><?= $id('details') ?>><?= h($details) ?></p>
  <?php endif; ?>

  <p class="cert-body<?= h($editClass) ?>" data-cert-field="closing"<?= $editAttr ?><?= $id('closing') ?>><?= h($closing) ?></p>

  <p class="cert-issued">Issued this
    <span class="cert-issued-date<?= h($editClass) ?>" data-cert-field="issuedDate"<?= $editAttr ?><?= $id('issued') ?>><?= h($issuedDate) ?></span>
    at Bacnotan, La Union.
  </p>

  <div class="cert-sign-row">
    <div class="cert-sign">
      <p class="cert-sign-label">Certified by:</p>
      <p class="cert-sign-name<?= h($editClass) ?>" data-cert-field="certifiedBy"<?= $editAttr ?>><?= h($certifiedBy) ?></p>
      <p class="cert-sign-role">Punong Barangay</p>
    </div>
    <div class="cert-dry-seal">SEAL</div>
  </div>

  <div class="cert-bottom">
    <div class="cert-official-receipt">
      <p class="cert-or-title">Official Receipt</p>
      <p><span>Control No.</span> <?= h($controlNo !== '' ? $controlNo : '—') ?></p>
      <p><span>Receipt No.</span> <?= h($receiptNo !== '' ? $receiptNo : '—') ?></p>
      <p><span>Amount</span> <?= h($receiptPrice !== '' ? $receiptPrice : '—') ?></p>
      <p><span>Date</span> <?= h($receiptDate !== '' ? $receiptDate : '—') ?></p>
    </div>
    <div class="cert-qr">
      <?php if ($qr !== ''): ?>
        <img src="<?= h($qr) ?>" alt="Scan to verify this certificate" />
      <?php else: ?>
        <div class="cert-qr-slot"></div>
      <?php endif; ?>
      <span>Scan to verify</span>
    </div>
  </div>
</div>
