(function () {
  const root = document.documentElement;
  const saved = localStorage.getItem('govly-theme') || 'light';
  root.setAttribute('data-theme', saved);

  function bindTheme() {
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
  }
  bindTheme();
})();
