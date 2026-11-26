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
if (!is_array($last)) {
    $last = [];
}
$type = $last['type'] ?? $req['certificate_type'];
$tpl = get_cert_template((string) $type);
$name = $last['recipientName'] ?? $req['resident_name'];
$address = $last['address'] ?? 'Barangay Sta. Cruz, Bacnotan, La Union';
$captain = db_one("SELECT name FROM officials WHERE position = 'Punong Barangay' LIMIT 1");
$control = $last['controlNo'] ?? make_control_no();
$body = $last['body'] ?? fill_placeholders($tpl['body'] ?? '', (string) $name, (string) $address);
$purpose = $last['purpose'] ?? ($req['purpose'] ?: ($tpl['purpose'] ?? ''));
$details = $last['details'] ?? ($tpl['details'] ?? '');
$closing = $last['closing'] ?? ($tpl['closing'] ?? '');
$issued = $last['issuedDate'] ?? issued_today();
$certified = $last['certifiedBy'] ?? strtoupper(trim((string) ($captain['name'] ?? '')));
$receipt = $last['receiptNo'] ?? ('OR-' . $control);
$amount = $last['amount'] ?? service_price($site, (string) $type);
$receiptDate = date('F j, Y');
$isWalkIn = !empty($req['walk_in']);
$savedOk = !empty($_GET['ok']);
$residentEmail = trim((string) ($req['email'] ?? ''));
if ($residentEmail === '' && !empty($req['user_id'])) {
    $emailRow = db_one('SELECT email FROM users WHERE id = ?', [(int) $req['user_id']]);
    $residentEmail = trim((string) ($emailRow['email'] ?? ''));
}
$title = 'Print Certificate — Barangay Sta. Cruz';
require __DIR__ . '/_head.php';
?>
<?php if ($savedOk): ?>
  <div class="flash ok no-print">
    Certificate saved with QR control number<?= $residentEmail !== '' ? ' · notice sent to <strong>' . h($residentEmail) . '</strong>' : '' ?>.
  </div>
<?php endif; ?>

<div class="print-desk">
  <div class="print-desk-bar no-print">
    <div>
      <p class="print-desk-kicker mb-1">Barangay Sta. Cruz · Bacnotan, La Union</p>
      <h1 class="print-desk-title mb-0">Preview &amp; print certificate</h1>
    </div>
    <a class="btn btn-sm btn-outline-light rounded-3" href="<?= h(base_url('admin?tab=requests')) ?>">← Back to requests</a>
  </div>

  <div class="container-xl px-3 py-4 print-desk-body">
    <div class="row g-4">
      <div class="col-xl-4 no-print">
        <form method="post" class="print-panel" id="printCertForm">
          <input type="hidden" name="action" value="print_cert" />
          <input type="hidden" name="id" value="<?= (int) $id ?>" />
          <input type="hidden" name="control_no" value="<?= h($control) ?>" />
          <input type="hidden" name="receipt_no" value="<?= h($receipt) ?>" />
          <input type="hidden" name="amount" value="<?= h($amount) ?>" />

          <h2 class="h6 fw-bold mb-3" style="color:#0038A8">Certificate details</h2>

          <div class="mb-3">
            <label class="form-label fw-semibold small">Resident name</label>
            <input class="form-control" name="recipient_name" id="fName" value="<?= h($name) ?>" required />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small">Certificate type</label>
            <input class="form-control" name="type" id="fType" value="<?= h($type) ?>" required />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small">Purpose</label>
            <input class="form-control" name="purpose" id="fPurpose" value="<?= h($purpose) ?>" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small">Certificate wording</label>
            <textarea class="form-control" name="body" id="fBody" rows="5" required><?= h($body) ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small">Additional details (optional)</label>
            <textarea class="form-control" name="details" id="fDetails" rows="2"><?= h($details) ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small">Closing</label>
            <textarea class="form-control" name="closing" id="fClosing" rows="2"><?= h($closing) ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small">Date issued</label>
            <input class="form-control" name="issued_date" id="fIssued" value="<?= h($issued) ?>" />
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small">Certified by (Punong Barangay)</label>
            <input class="form-control" name="certified_by" id="fCertified" value="<?= h($certified) ?>" placeholder="FULL NAME" />
          </div>

          <div class="print-meta mb-3">
            <div><span>Control No.</span><strong><?= h($control) ?></strong></div>
            <div><span>Receipt No.</span><strong><?= h($receipt) ?></strong></div>
            <div><span>Amount</span><strong><?= h($amount ?: '—') ?></strong></div>
            <div><span>Request</span><strong>#<?= (int) $id ?><?= $isWalkIn ? ' · Walk-in' : '' ?></strong></div>
          </div>

          <input type="hidden" name="mark_completed" id="markCompleted" value="1" />

          <div class="alert mb-3 py-2 px-3" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1e3a8a;font-size:.85rem">
            <?php if ($residentEmail !== ''): ?>
              After you print, this certificate is saved automatically and a notice is emailed to
              <strong><?= h($residentEmail) ?></strong>.
            <?php else: ?>
              After you print, this certificate is saved automatically. No resident email is on file.
            <?php endif; ?>
          </div>

          <div class="d-grid gap-2">
            <button class="btn fw-bold rounded-3 py-2" style="background:#0038A8;color:#fff" type="button" id="btnPrintNow">
              Print certificate
            </button>
            <a class="btn btn-outline-secondary rounded-3" href="<?= h(base_url('admin?tab=requests')) ?>">Cancel</a>
          </div>

          <p class="text-muted small mt-3 mb-0">
            Review the preview on the right, then print on letter-size paper. QR control number is saved automatically after printing.
          </p>
        </form>
      </div>

      <div class="col-xl-8 print-preview-col">
        <div class="print-preview-wrap">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
            <div>
              <h2 class="h6 fw-bold mb-0" style="color:#0038A8">Official print preview</h2>
              <p class="text-muted small mb-0">This is how the certificate will appear on paper.</p>
            </div>
            <button type="button" class="btn btn-sm fw-bold rounded-3" style="background:#0038A8;color:#fff" id="btnPrintTop">Print</button>
          </div>
          <div class="print-preview-frame" id="printPreviewFrame">
            <?php
              $cert = [
                'type' => $type,
                'body' => $body,
                'purpose' => $purpose,
                'details' => $details,
                'closing' => $closing,
                'issuedDate' => $issued,
                'certifiedBy' => $certified,
                'controlNo' => $control,
                'receiptNo' => $receipt,
                'receiptPrice' => $amount,
                'receiptDate' => $receiptDate,
                'recipientName' => $name,
                'address' => $address,
              ];
              $certEditable = false;
              $certIds = true;
              require __DIR__ . '/_cert_template.php';
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade no-print" id="printEmailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header" style="background:linear-gradient(135deg,#0038A8,#002570);color:#fff">
        <h5 class="modal-title fw-bold">Print certificate</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2 fw-semibold">You are about to print this certificate.</p>
        <p class="text-muted small">After printing, the QR control number is saved automatically<?= $residentEmail !== '' ? ' and a completion notice is emailed to the resident' : '' ?>.</p>
        <?php if ($residentEmail !== ''): ?>
          <div class="rounded-3 p-3 mb-0" style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46">
            Email notice will be sent to:
            <div class="fw-bold mt-1"><?= h($residentEmail) ?></div>
          </div>
        <?php else: ?>
          <div class="rounded-3 p-3 mb-0" style="background:#fff7ed;border:1px solid #fed7aa;color:#9a3412">
            No email is on file for this resident — print and save only.
          </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary rounded-3 fw-semibold" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn rounded-3 fw-bold text-white" style="background:#0038A8" id="confirmPrintBtn">
          <?= $residentEmail !== '' ? 'Print, save &amp; email' : 'Print &amp; save' ?>
        </button>
      </div>
    </div>
  </div>
</div>

<style>
body { background: #eef2f7; }
.print-desk-bar {
  background: linear-gradient(135deg, #0038A8 0%, #002570 55%, #001a47 100%);
  color: #fff;
  padding: 1rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}
.print-desk-kicker {
  text-transform: uppercase;
  letter-spacing: .1em;
  font-size: .7rem;
  opacity: .85;
}
.print-desk-title {
  font-size: 1.25rem;
  font-weight: 800;
}
.print-panel {
  background: #fff;
  border: 1px solid #e4ebf3;
  border-radius: 1rem;
  padding: 1.25rem;
  box-shadow: 0 10px 28px rgba(15,23,42,.06);
}
.print-meta {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: .75rem;
  padding: .85rem 1rem;
  display: grid;
  gap: .45rem;
  font-size: .85rem;
}
.print-meta span { color: #64748b; display: inline-block; min-width: 5.5rem; }
.print-preview-wrap {
  background: #fff;
  border: 1px solid #e4ebf3;
  border-radius: 1rem;
  padding: 1.25rem;
  box-shadow: 0 10px 28px rgba(15,23,42,.06);
}
.print-preview-frame {
  background: #f1f5f9;
  border: 1px solid #dbe3ee;
  border-radius: .75rem;
  padding: 1rem;
  max-height: min(78vh, 920px);
  overflow: auto;
}
.print-preview-frame .cert-sheet {
  margin: 0 auto;
  transform-origin: top center;
}
.print-only-sheet { display: none; }
@media (max-width: 1200px) {
  .print-preview-frame .cert-sheet {
    transform: scale(0.82);
    transform-origin: top center;
    margin-bottom: -2.5in;
  }
}
@media print {
  @page { size: letter; margin: 0; }
  html, body {
    background: #fff !important;
    margin: 0 !important;
    padding: 0 !important;
    height: auto !important;
    overflow: visible !important;
  }
  .no-print, .flash, .site-nav, .nav-spacer { display: none !important; }
  .print-desk, .print-desk-body, .print-preview-col, .print-preview-wrap, .print-preview-frame, .row, .container-xl {
    display: block !important;
    width: 100% !important;
    max-width: none !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    margin: 0 !important;
    max-height: none !important;
    overflow: visible !important;
    background: #fff !important;
  }
  .print-preview-frame .cert-sheet {
    display: block !important;
    visibility: visible !important;
    transform: none !important;
    margin: 0 auto !important;
    box-shadow: none !important;
    width: 8.5in !important;
    min-height: 11in !important;
  }
  .cert-editable { outline: none !important; background: transparent !important; padding: 0 !important; }
}
</style>
<script>
(function () {
  var map = [
    ['fType', 'pvType'],
    ['fBody', 'pvBody'],
    ['fPurpose', 'pvPurpose'],
    ['fDetails', 'pvDetails'],
    ['fClosing', 'pvClosing'],
    ['fIssued', 'pvIssued']
  ];
  function sync() {
    map.forEach(function (pair) {
      var input = document.getElementById(pair[0]);
      var preview = document.getElementById(pair[1]);
      if (input && preview) preview.textContent = input.value;
    });
    var certified = document.getElementById('fCertified');
    var sign = document.querySelector('[data-cert-field="certifiedBy"]');
    if (certified && sign) sign.textContent = certified.value;
  }
  map.forEach(function (pair) {
    var input = document.getElementById(pair[0]);
    if (input) input.addEventListener('input', sync);
  });
  var certified = document.getElementById('fCertified');
  if (certified) certified.addEventListener('input', sync);
  sync();

  var saving = false;

  function saveAfterPrint() {
    if (saving) return;
    saving = true;
    sync();
    var mark = document.getElementById('markCompleted');
    var form = document.getElementById('printCertForm');
    if (mark) mark.value = '1';
    if (form) form.submit();
  }

  function openPrintModal() {
    sync();
    var el = document.getElementById('printEmailModal');
    if (el && window.bootstrap) {
      bootstrap.Modal.getOrCreateInstance(el).show();
      return;
    }
    confirmAndPrint();
  }

  function confirmAndPrint() {
    sync();
    var el = document.getElementById('printEmailModal');
    if (el && window.bootstrap) {
      var modal = bootstrap.Modal.getInstance(el);
      if (modal) modal.hide();
    }
    // Print first; save (+ email) automatically when the print dialog closes
    var done = false;
    function finish() {
      if (done) return;
      done = true;
      window.removeEventListener('afterprint', finish);
      saveAfterPrint();
    }
    window.addEventListener('afterprint', finish);
    setTimeout(function () {
      window.print();
      // Fallback if afterprint does not fire in this browser
      setTimeout(finish, 1200);
    }, 200);
  }

  var b1 = document.getElementById('btnPrintNow');
  var b2 = document.getElementById('btnPrintTop');
  var confirmBtn = document.getElementById('confirmPrintBtn');
  if (b1) b1.addEventListener('click', openPrintModal);
  if (b2) b2.addEventListener('click', openPrintModal);
  if (confirmBtn) confirmBtn.addEventListener('click', confirmAndPrint);
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
