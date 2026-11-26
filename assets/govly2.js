(function () {
  const root = document.documentElement;
  const saved = localStorage.getItem('govly-theme') || 'light';
  root.setAttribute('data-theme', saved);

  document.querySelectorAll('#themeToggle').forEach((btn) => {
    btn.addEventListener('click', () => {
      const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      localStorage.setItem('govly-theme', next);
      btn.textContent = next === 'dark' ? '☀' : '☾';
      btn.title = next === 'dark' ? 'Light mode' : 'Night mode';
    });
    btn.textContent = root.getAttribute('data-theme') === 'dark' ? '☀' : '☾';
  });

  // Exact Capstone Finale HeroMap.jsx
  const heroEl = document.getElementById('heroMap');
  if (heroEl && typeof L !== 'undefined') {
    const STA_CRUZ = [16.74073, 120.34599];
    const map = L.map(heroEl, {
      center: STA_CRUZ,
      zoom: 15,
      zoomControl: false,
      attributionControl: false,
      dragging: false,
      scrollWheelZoom: false,
      doubleClickZoom: false,
      boxZoom: false,
      keyboard: false,
      touchZoom: false,
    });
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    const resize = () => map.invalidateSize();
    requestAnimationFrame(resize);
    setTimeout(resize, 200);
    window.addEventListener('resize', resize);
    if (typeof ResizeObserver !== 'undefined') new ResizeObserver(resize).observe(heroEl);
  }

  // Open-Meteo weather for Bacnotan (same as Capstone Finale)
  const wxCard = document.getElementById('weatherCard');
  if (!wxCard) return;

  const TZ = 'Asia/Manila';
  const URL =
    'https://api.open-meteo.com/v1/forecast'
    + '?latitude=16.7299&longitude=120.3497'
    + '&current=temperature_2m,relative_humidity_2m,weather_code,cloud_cover,pressure_msl,wind_speed_10m,wind_gusts_10m,visibility,is_day'
    + '&daily=sunrise,sunset&forecast_days=1&wind_speed_unit=mph'
    + '&timezone=' + encodeURIComponent(TZ);

  function describe(code, isDay) {
    if (code === 0 || code === 1) return isDay ? 'Clear Sky' : 'Clear Night';
    if (code === 2) return 'Scattered Clouds';
    if (code === 3) return 'Overcast Clouds';
    if (code === 45 || code === 48) return 'Fog';
    if (code >= 51 && code <= 57) return 'Drizzle';
    if (code === 61 || code === 80) return 'Light Rain';
    if (code === 63 || code === 81) return 'Moderate Rain';
    if (code === 65 || code === 82) return 'Heavy Rain';
    if (code >= 95) return 'Thunderstorm';
    return 'Cloudy';
  }

  function iconFor(code, isDay) {
    if (code === 0 || code === 1) return isDay ? '☀' : '☾';
    if (code === 2 || code === 3) return '☁';
    if (code >= 51 && code <= 67) return '🌧';
    if (code >= 80 && code <= 82) return '🌦';
    if (code >= 95) return '⛈';
    return '☁';
  }

  function clock() {
    const now = new Date();
    const time = now.toLocaleTimeString('en-US', { timeZone: TZ, hour: 'numeric', minute: '2-digit' }).toLowerCase();
    const day = now.toLocaleDateString('en-US', { timeZone: TZ, month: 'short', day: 'numeric', year: 'numeric' });
    const el = document.getElementById('wxClock');
    if (el) el.textContent = time + ', ' + day;
  }

  function hm(iso) {
    if (!iso) return '—';
    const parts = iso.slice(11, 16).split(':').map(Number);
    const h = parts[0];
    const m = parts[1];
    const suffix = h >= 12 ? 'pm' : 'am';
    return (h % 12 || 12) + ':' + String(m).padStart(2, '0') + ' ' + suffix;
  }

  function load() {
    fetch(URL)
      .then((r) => (r.ok ? r.json() : Promise.reject()))
      .then((data) => {
        const cur = data.current;
        if (!cur) throw new Error('no current');
        document.getElementById('wxLoading').style.display = 'none';
        document.getElementById('wxBody').style.display = 'block';
        document.getElementById('wxTemp').textContent = Math.round(cur.temperature_2m);
        document.getElementById('wxDesc').textContent = describe(cur.weather_code, cur.is_day === 1);
        document.getElementById('wxIcon').textContent = iconFor(cur.weather_code, cur.is_day === 1);
        document.getElementById('wxHum').textContent = '💧 ' + Math.round(cur.relative_humidity_2m) + '%';
        document.getElementById('wxPress').textContent = '◎ ' + Math.round(cur.pressure_msl) + ' mb';
        document.getElementById('wxWind').textContent = '🌬 ' + Math.round(cur.wind_speed_10m) + ' mph';
        document.getElementById('wxGust').textContent = Math.round(cur.wind_gusts_10m) + ' mph';
        document.getElementById('wxCloud').textContent = Math.round(cur.cloud_cover) + '%';
        document.getElementById('wxVis').textContent = Math.round(cur.visibility / 1000) + ' km';
        document.getElementById('wxRise').textContent = hm(data.daily && data.daily.sunrise && data.daily.sunrise[0]);
        document.getElementById('wxSet').textContent = hm(data.daily && data.daily.sunset && data.daily.sunset[0]);
      })
      .catch(() => {
        const el = document.getElementById('wxLoading');
        if (el) el.textContent = 'Weather data is unavailable right now.';
      });
  }

  clock();
  load();
  setInterval(clock, 30000);
  setInterval(load, 10 * 60 * 1000);
})();

(function () {
  document.querySelectorAll('[data-news-carousel]').forEach((root) => {
    const track = root.querySelector('[data-news-track]');
    const prev = root.querySelector('[data-news-prev]');
    const next = root.querySelector('[data-news-next]');
    const dotsWrap = root.querySelector('[data-news-dots]');
    if (!track) return;

    const cards = Array.from(track.children);
    if (!cards.length) return;

    const step = () => {
      const card = cards[0];
      const styles = window.getComputedStyle(track);
      const gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
      return card.getBoundingClientRect().width + gap;
    };

    const pageCount = () => Math.max(1, Math.ceil(track.scrollWidth / Math.max(track.clientWidth, 1)));

    const renderDots = () => {
      if (!dotsWrap) return;
      const pages = pageCount();
      dotsWrap.innerHTML = '';
      for (let i = 0; i < pages; i += 1) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.setAttribute('aria-label', 'Go to news page ' + (i + 1));
        btn.addEventListener('click', () => {
          track.scrollTo({ left: i * track.clientWidth, behavior: 'smooth' });
        });
        dotsWrap.appendChild(btn);
      }
      syncDots();
    };

    const syncDots = () => {
      if (!dotsWrap) return;
      const pages = pageCount();
      const idx = Math.min(
        pages - 1,
        Math.max(0, Math.round(track.scrollLeft / Math.max(track.clientWidth, 1)))
      );
      Array.from(dotsWrap.children).forEach((el, i) => {
        el.classList.toggle('active', i === idx);
      });
    };

    if (prev) {
      prev.addEventListener('click', () => {
        track.scrollBy({ left: -step(), behavior: 'smooth' });
      });
    }
    if (next) {
      next.addEventListener('click', () => {
        track.scrollBy({ left: step(), behavior: 'smooth' });
      });
    }
    track.addEventListener('scroll', () => {
      window.clearTimeout(track._newsDotTimer);
      track._newsDotTimer = window.setTimeout(syncDots, 80);
    }, { passive: true });
    window.addEventListener('resize', renderDots);
    renderDots();
  });
})();
