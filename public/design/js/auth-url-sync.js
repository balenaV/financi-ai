/* financiaí — extensão do backend sobre o pacote de design.
   Mantém a URL sincronizada com a aba visível (Entrar/Criar conta) sem recarregar
   a página. Não faz parte do handoff/js/auth.js: fica em arquivo separado para
   não editar o script copiado do pacote de referência. */

(function () {
  var tabs = document.querySelector('[data-tabs]');
  if (!tabs) return;

  tabs.querySelectorAll('[data-tab]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var url = btn.getAttribute('data-url');
      if (!url) return;
      if (window.location.href !== url) {
        history.replaceState(null, '', url);
      }
    });
  });
})();
