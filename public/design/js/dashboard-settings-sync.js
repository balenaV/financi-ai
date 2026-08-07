/* financiaí — extensão do backend sobre o pacote de design.
   Persiste tema e "ocultar valores" no servidor (por usuário), além da troca
   visual instantânea já feita por theme.js/dashboard.js. Arquivo novo: não
   edita os scripts copiados do pacote de referência. */

(function () {
  var csrf = document.querySelector('meta[name="csrf-token"]');
  if (!csrf) return;
  var token = csrf.getAttribute('content');

  function persist(url) {
    fetch(url, {
      method: 'PATCH',
      headers: {
        'X-CSRF-TOKEN': token,
        Accept: 'application/json',
      },
    });
  }

  document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
    var url = btn.getAttribute('data-toggle-url');
    if (!url) return;
    btn.addEventListener('click', function () { persist(url); });
  });

  var eyeBtn = document.querySelector('[data-toggle-money]');
  if (eyeBtn) {
    var url = eyeBtn.getAttribute('data-toggle-url');
    if (url) eyeBtn.addEventListener('click', function () { persist(url); });
  }

  /* Abre a aba certa quando chega de outra página com #aba na URL
     (ex: link "Voltar para Contas" da importação). */
  if (location.hash) {
    var target = document.querySelector('[data-goto="' + location.hash.slice(1) + '"]');
    if (target) target.click();
  }

  /* Chips "Claro/Escuro" em Configurações › Preferências: o dashboard.js do
     pacote só troca o atributo data-theme na hora (e grava numa chave de
     localStorage diferente da que theme.js lê). Aqui persistimos de verdade
     no servidor — só quando o valor muda — e mantemos o input oculto do
     formulário de preferências sincronizado, para o "Salvar preferências"
     não reverter uma troca de tema feita momentos antes. */
  var themeSetButtons = document.querySelectorAll('[data-theme-set]');
  if (themeSetButtons.length) {
    var currentTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    var themeToggle = document.querySelector('[data-theme-toggle]');
    var themeToggleUrl = themeToggle ? themeToggle.getAttribute('data-toggle-url') : null;
    var themeInput = document.querySelector('[data-theme-input]');

    themeSetButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var target = btn.getAttribute('data-theme-set');
        try { localStorage.setItem('financiai:theme', target); } catch (e) {}
        if (themeInput) themeInput.value = target;
        if (target === currentTheme) return;
        currentTheme = target;
        if (themeToggleUrl) persist(themeToggleUrl);
      });
    });
  }
})();
