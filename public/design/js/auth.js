/* financiaí — comportamento da tela de autenticação.
   Sem dependências. Todo estado vive em atributos data-* lidos pelo CSS. */

(function () {
  var root = document.documentElement;

  /* ---------- Tema ---------- */
  var STORAGE_KEY = 'financiai:theme';
  var saved = null;
  try { saved = localStorage.getItem(STORAGE_KEY); } catch (e) {}
  if (saved === 'dark' || saved === 'light') root.setAttribute('data-theme', saved);

  function syncThemeIcon() {
    var dark = root.getAttribute('data-theme') === 'dark';
    document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
      btn.textContent = dark ? '☀' : '☾';
    });
  }

  document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      try { localStorage.setItem(STORAGE_KEY, next); } catch (e) {}
      syncThemeIcon();
    });
  });
  syncThemeIcon();

  /* ---------- Abas login / criar conta ---------- */
  var tabs = document.querySelector('[data-tabs]');
  var titulo = document.querySelector('[data-auth-title]');
  var subtitulo = document.querySelector('[data-auth-subtitle]');

  var COPY = {
    login: {
      titulo: 'Bem-vindo de volta.',
      subtitulo: 'Entre para acompanhar suas contas, metas e compromissos dos próximos meses.'
    },
    registro: {
      titulo: 'Comece a organizar hoje.',
      subtitulo: 'Crie sua conta gratuita e registre sua realidade financeira em poucos minutos.'
    }
  };

  function setModo(modo) {
    if (!tabs) return;
    tabs.setAttribute('data-active', modo);
    tabs.querySelectorAll('[data-tab]').forEach(function (btn) {
      btn.setAttribute('aria-selected', String(btn.dataset.tab === modo));
    });
    document.querySelectorAll('[data-pane]').forEach(function (form) {
      form.setAttribute('data-state', form.dataset.pane === modo ? 'active' : 'hidden');
    });
    if (titulo) titulo.textContent = COPY[modo].titulo;
    if (subtitulo) subtitulo.textContent = COPY[modo].subtitulo;
  }

  if (tabs) {
    tabs.querySelectorAll('[data-tab]').forEach(function (btn) {
      btn.addEventListener('click', function () { setModo(btn.dataset.tab); });
    });
  }

  /* ---------- Mostrar / ocultar senha ---------- */
  document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var group = btn.closest('.input-group');
      if (!group) return;
      var visivel = group.querySelector('input').type === 'text';
      group.querySelectorAll('input').forEach(function (input) {
        input.type = visivel ? 'password' : 'text';
      });
      btn.textContent = visivel ? 'Mostrar' : 'Ocultar';
    });
  });
})();
