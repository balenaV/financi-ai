/* financiaí — tema compartilhado por todas as telas.
   Carregar antes dos demais scripts. */

(function () {
  var root = document.documentElement;
  var KEY = 'financiai:theme';

  var saved = null;
  try { saved = localStorage.getItem(KEY); } catch (e) {}
  if (saved === 'dark' || saved === 'light') root.setAttribute('data-theme', saved);

  function icon() {
    var dark = root.getAttribute('data-theme') === 'dark';
    document.querySelectorAll('[data-theme-toggle]').forEach(function (b) {
      b.textContent = dark ? '☀' : '☾';
    });
  }

  document.querySelectorAll('[data-theme-toggle]').forEach(function (b) {
    b.addEventListener('click', function () {
      var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      try { localStorage.setItem(KEY, next); } catch (e) {}
      icon();
    });
  });

  icon();
})();
