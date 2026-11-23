<?php
$title = 'Barangay Sta.Cruz Portal';
$site = site();
$officials = [];
$anns = [];
$services = $site['services'] ?? [];
try {
    $officials = db_all('SELECT * FROM officials ORDER BY order_num, name');
    $anns = db_all('SELECT * FROM announcements ORDER BY created_at DESC LIMIT 6');
} catch (Throwable $e) {}
$captains = array_values(array_filter($officials, fn($o) => $o['position'] === 'Punong Barangay'));
$kagawads = array_values(array_filter($officials, fn($o) => $o['position'] === 'Kagawad'));
$staff = array_values(array_filter($officials, fn($o) => in_array($o['position'], ['Barangay Secretary', 'Barangay Treasurer'], true)));
$others = array_values(array_filter($officials, fn($o) => !in_array($o['position'], ['Punong Barangay', 'Kagawad', 'Barangay Secretary', 'Barangay Treasurer'], true)));
$icons = ['📄','🏠','🤝','💼','👤','💛'];

$hotlines = [
    ['911', 'tel:911', 'La Union Hotline', 'Telephone'],
    ['0919-546-1800', 'tel:09195461800', 'Bacnotan Rescue', 'Smart'],
    ['0949-474-2161', 'tel:09494742161', 'MDRRMO', 'Smart'],
    ['0998-598-5156', 'tel:09985985156', 'PNP – Bacnotan', 'Smart'],
    ['0917-184-2711', 'tel:09171842711', 'BFP – Bacnotan', 'Globe'],
];
$bacnotanMap = 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d51975.24443365773!2d120.34348082346072!3d16.749856342903477!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3391ed51813b1759%3A0x7292eea3c59c6bde!2sBacnotan%2C%20La%20Union!5e1!3m2!1sen!2sph!4v1774850595468!5m2!1sen!2sph';
$evacMap = 'https://www.google.com/maps/d/embed?mid=1ojLIKdvBAFRfbtzfSw1o747gd3FyjZ8&ehbc=2E312F&noprof=1';

require __DIR__ . '/_head.php';
require __DIR__ . '/_nav.php';
?>
<main>
  <section id="home" class="hero-section">
    <div class="hero-media" aria-hidden="true">
      <img class="hero-map-static" src="<?= h(base_url('assets/hero-map-navy.jpg')) ?>?v=5" alt="" width="1440" height="900" decoding="async" fetchpriority="high" />
    </div>
    <div class="hero-overlay"></div>
    <p class="hero-map-credit">Barangay Sta. Cruz, Bacnotan, La Union · © OpenStreetMap</p>
    <div class="hero-inner container-xl px-3">
      <div class="hero-copy">
        <h1 class="hero-title">Barangay Sta. Cruz</h1>
        <p class="hero-place">Bacnotan, La Union</p>
        <p class="hero-lead">
          Welcome to the official online portal of Barangay Sta. Cruz. Access essential government services,
          request documents, stay updated on community news, and connect with your local government.
        </p>
        <div class="hero-btn-row">
          <a class="hero-btn hero-btn-primary" href="#services">Browse Services</a>
          <a class="hero-btn hero-btn-ghost" href="#contact">Contact Us</a>
        </div>
      </div>
      <div class="hero-stats">
        <a class="hero-stat" href="https://psa.gov.ph/content/2024-census-population-popcen-population-counts-declared-official-president" target="_blank" rel="noreferrer">
          <div class="hero-stat-num">1,279</div>
          <div class="hero-stat-label">Population</div>
          <div class="hero-stat-sub">2024 POPCEN</div>
        </a>
        <div class="hero-stat">
          <div class="hero-stat-num">7</div>
          <div class="hero-stat-label">Puroks</div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-num"><?= count($services) ?></div>
          <div class="hero-stat-label">Services Available</div>
        </div>
        <a class="hero-stat hero-stat-action" href="#weather">
          <div class="hero-stat-num">Map</div>
          <div class="hero-stat-label">View Location</div>
        </a>
      </div>
    </div>
  </section>

  <section id="services" class="page-section" style="background:#f8f9fa">
    <div class="container-xl px-3">
      <div class="section-intro text-center">
        <span class="pill pill-blue">Government Services</span>
        <h2 class="bac-heading">Available Services</h2>
        <p class="bac-lead">Access our most commonly requested services online. Apply for documents, permits, and certificates with ease.</p>
      </div>
      <div class="row g-4">
        <?php foreach ($services as $i => $s): ?>
          <div class="col-12 col-md-6 col-lg-4">
            <a class="service-tile text-decoration-none d-block" href="<?= h(base_url(current_user() ? 'dashboard' : 'login')) ?>">
              <div class="service-icon"><?= $icons[$i % count($icons)] ?></div>
              <h5 style="font-size:1.2rem;font-weight:600;margin-bottom:12px;color:#003d82"><?= h($s['type'] ?? '') ?></h5>
              <p style="font-size:.95rem;color:#666;margin-bottom:20px;line-height:1.6"><?= h($s['desc'] ?? '') ?></p>
              <div class="d-flex justify-content-between align-items-center pt-3" style="border-top:1px solid #f0f0f0">
                <span class="service-price"><?= h($s['price'] ?? '') ?></span>
                <span class="service-time">⏱ <?= h($s['time'] ?? '') ?></span>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="officials" class="page-section" style="background:#fff">
    <div class="container-xl px-3">
      <div class="section-intro text-center">
        <span class="pill pill-purple">Leadership</span>
        <h2 class="bac-heading">Barangay Officials</h2>
        <p class="bac-lead">Meet the dedicated public servants leading our community towards progress and development.</p>
      </div>
      <?php if (!$officials): ?>
        <p class="text-center text-muted py-4">No officials added yet.</p>
      <?php else:
        $render = function (array $list) {
            echo '<div class="official-row">';
            foreach ($list as $o) {
                $title = $o['position'] === 'Kagawad' && (int)$o['order_num'] < 90 ? 'Kagawad ' . (int)$o['order_num'] : $o['position'];
                echo '<div class="official-card">';
                if (!empty($o['photo_url'])) echo '<img src="' . h(base_url($o['photo_url'])) . '" alt="" />';
                else echo '<div class="official-fallback">' . h(mb_substr($o['name'], 0, 1)) . '</div>';
                echo '<strong>' . h($o['name']) . '</strong><div class="text-muted small">' . h($title) . '</div></div>';
            }
            echo '</div>';
        };
        if ($captains) $render($captains);
        if ($kagawads) $render($kagawads);
        if ($staff) $render($staff);
        if ($others) $render($others);
      endif; ?>
    </div>
  </section>

  <section id="announcements" class="page-section" style="background:#f8f9fa">
    <div class="container-xl px-3">
      <div class="section-intro text-center">
        <span class="pill pill-red">Latest News</span>
        <h2 class="bac-heading">News &amp; Announcements</h2>
        <p class="bac-lead">Stay informed about important notices, community events, and government updates.</p>
      </div>
      <?php if (!$anns): ?>
        <p class="text-center text-muted">No announcements yet.</p>
      <?php else: ?>
        <div class="row g-4">
          <div class="col-12 col-md-8">
            <div class="p-4 text-white rounded-3" style="background:linear-gradient(135deg,#003d82,#005ba3)">
              <span class="badge mb-2" style="background:#ffc107;color:#003d82">FEATURED</span>
              <h3 class="fw-bold"><?= h($anns[0]['title']) ?></h3>
              <p><?= h(mb_substr((string)$anns[0]['body'], 0, 220)) ?>…</p>
            </div>
          </div>
          <div class="col-12 col-md-4 d-flex flex-column gap-3">
            <?php foreach (array_slice($anns, 1, 3) as $a): ?>
              <div class="card-soft">
                <div class="small fw-bold" style="color:#ffc107"><?= h(date('M j, Y', strtotime($a['created_at']))) ?></div>
                <strong><?= h($a['title']) ?></strong>
                <p class="small text-muted mb-0"><?= h(mb_substr((string)$a['body'], 0, 80)) ?>…</p>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section id="weather" class="page-section bac-section">
    <div class="container-xl px-3">
      <div class="section-intro text-center">
        <span class="pill pill-blue">Municipality</span>
        <h2 class="bac-heading">Weather and Map of Bacnotan, La Union</h2>
        <p class="bac-lead">Live weather for Bacnotan and a municipal map for orientation around Barangay Sta. Cruz.</p>
      </div>
      <div class="row g-4 align-items-stretch">
        <div class="col-lg-4">
          <div class="bac-weather" id="weatherCard">
            <div class="bac-weather-head">
              <div>
                <p class="bac-weather-kicker">Live conditions</p>
                <h3>Bacnotan, PH</h3>
                <p id="wxClock">—</p>
              </div>
              <span class="bac-weather-badge">Open-Meteo</span>
            </div>
            <p class="bac-weather-loading" id="wxLoading">Loading weather…</p>
            <div id="wxBody" style="display:none">
              <div class="bac-weather-now">
                <span class="bac-weather-icon" id="wxIcon">☁</span>
                <div>
                  <span class="bac-weather-temp"><span id="wxTemp">—</span><small>°C</small></span>
                  <p class="bac-weather-desc" id="wxDesc">—</p>
                </div>
              </div>
              <div class="bac-weather-quick">
                <span id="wxHum">—%</span>
                <span id="wxPress">— mb</span>
                <span id="wxWind">— mph</span>
              </div>
              <ul class="bac-weather-list">
                <li><span>Wind Gust</span><strong id="wxGust">—</strong></li>
                <li><span>Clouds</span><strong id="wxCloud">—</strong></li>
                <li><span>Visibility</span><strong id="wxVis">—</strong></li>
                <li><span>Sunrise</span><strong id="wxRise">—</strong></li>
                <li><span>Sunset</span><strong id="wxSet">—</strong></li>
              </ul>
            </div>
            <p class="bac-weather-attr">Weather from <a href="https://open-meteo.com/" target="_blank" rel="noopener noreferrer">Open-Meteo</a></p>
          </div>
        </div>
        <div class="col-lg-8">
          <div class="bac-map-frame">
            <iframe class="bac-map" title="Map of Bacnotan, La Union" src="<?= h($bacnotanMap) ?>" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="emergency-services" class="page-section bac-section bac-section-alt">
    <div class="container-xl px-3">
      <div class="section-intro text-center">
        <span class="pill pill-blue">Public safety</span>
        <h2 class="bac-heading">Emergency Services</h2>
        <p class="bac-lead">Evacuation centers across Bacnotan and the main municipal hotlines. Call 911 first when life, fire, or crime is involved.</p>
      </div>
      <div class="row g-4 align-items-stretch">
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
            <a class="bac-hotline bac-hotline-more" href="<?= h(base_url('emergency')) ?>">
              <span class="bac-hotline-num">☎ Emergency Hotlines</span>
              <span class="bac-hotline-net">Browse complete hotlines</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section id="spot-map" class="page-section" style="background:#fff">
    <div class="container-xl px-3">
      <div class="section-intro text-center">
        <span class="pill pill-blue">Location</span>
        <h2 class="bac-heading">Barangay Spot Map</h2>
        <p class="bac-lead">Spot map of Barangay Sta. Cruz — roads follow OpenStreetMap, with local landmarks inside the barangay.</p>
      </div>
      <div class="bac-map-frame">
        <iframe class="bac-map" title="Sta. Cruz map" loading="lazy"
          src="https://www.openstreetmap.org/export/embed.html?bbox=120.330%2C16.730%2C120.360%2C16.750&amp;layer=mapnik&amp;marker=16.74078%2C120.34589"></iframe>
      </div>
    </div>
  </section>

  <section id="contact" class="page-section" style="background:#f8f9fa">
    <div class="container-xl px-3">
      <div class="section-intro text-center">
        <span class="pill pill-blue">Contact</span>
        <h2 class="bac-heading">Get in Touch</h2>
        <p class="bac-lead">Have questions? We're here to help. Reach out through any of the channels below.</p>
      </div>
      <div class="row g-4">
        <div class="col-12 col-lg-5">
          <div class="card-soft mb-3"><h6 class="fw-bold">Office Address</h6><p class="mb-0"><?= nl2br(h($site['officeAddress'] ?? '')) ?></p></div>
          <div class="card-soft mb-3"><h6 class="fw-bold">Phone Numbers</h6><p class="mb-0"><?= nl2br(h($site['contactPhones'] ?? '')) ?></p></div>
          <div class="card-soft mb-3"><h6 class="fw-bold">Email Address</h6><p class="mb-0"><?= h($site['contactEmail'] ?? '') ?></p></div>
          <div class="card-soft"><h6 class="fw-bold">Office Hours</h6><p class="mb-0"><?= nl2br(h($site['officeHours'] ?? '')) ?></p></div>
        </div>
        <div class="col-12 col-lg-7">
          <form class="card-soft" method="post">
            <h4 class="fw-bold" style="color:#003d82">Send us a Message</h4>
            <p class="text-muted">We'll respond to your inquiry within 24 hours.</p>
            <input type="hidden" name="action" value="contact" />
            <div class="mb-3"><label class="form-label">Full Name</label><input class="form-control" name="name" required placeholder="Your name" /></div>
            <div class="mb-3"><label class="form-label">Email Address</label><input class="form-control" type="email" name="email" required placeholder="your@email.com" /></div>
            <div class="mb-3">
              <label class="form-label">Subject</label>
              <select class="form-select" name="subject" required>
                <option value="">Select a subject</option>
                <option>General Inquiry</option>
                <option>Document Request</option>
                <option>Complaint</option>
                <option>Suggestion</option>
                <option>Other</option>
              </select>
            </div>
            <div class="mb-3"><label class="form-label">Message</label><textarea class="form-control" name="message" rows="4" required placeholder="Your message..."></textarea></div>
            <button class="btn btn-warning fw-bold" type="submit">Send Message</button>
          </form>
        </div>
      </div>
    </div>
  </section>
</main>

<footer class="text-white pt-5 pb-4" style="background:linear-gradient(135deg,#0038A8,#001a47)">
  <div class="container-xl px-3">
    <div class="row g-4">
      <div class="col-md-5">
        <h5 class="fw-bold">Barangay Sta. Cruz</h5>
        <p class="opacity-75">Committed to serving our community with transparency, efficiency, and compassion. Your trust is our greatest responsibility.</p>
      </div>
      <div class="col-md-3">
        <h6 class="fw-bold">Quick Links</h6>
        <ul class="list-unstyled">
          <li><a class="text-white text-decoration-none" href="#home">Home</a></li>
          <li><a class="text-white text-decoration-none" href="#services">Services</a></li>
          <li><a class="text-white text-decoration-none" href="#officials">Officials</a></li>
          <li><a class="text-white text-decoration-none" href="#emergency-services">Emergency</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h6 class="fw-bold">Contact</h6>
        <p class="mb-1 opacity-75">📞 <?= h(explode("\n", $site['contactPhones'] ?? '')[0] ?? '') ?></p>
        <p class="mb-1 opacity-75">📧 <?= h($site['contactEmail'] ?? '') ?></p>
        <p class="mb-0 opacity-75">📍 <?= h($site['footerLocation'] ?? 'Bacnotan, La Union') ?></p>
      </div>
    </div>
    <hr class="border-light opacity-25 my-4" />
    <p class="mb-0 small opacity-75 text-center">© <?= date('Y') ?> Barangay Sta. Cruz, Bacnotan, La Union. All rights reserved.</p>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= h(base_url('assets/govly2.js')) ?>?v=5"></script>
</body></html>
