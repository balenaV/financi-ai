/* financiaí — comportamento do dashboard.
   Sem dependências além de theme.js. */

(function () {

  /* ---------- Barra lateral compacta ---------- */
  var sidebar = document.querySelector('[data-sidebar]');
  if (sidebar) {
    document.querySelectorAll('[data-sidebar-toggle]').forEach(function (b) {
      b.addEventListener('click', function () {
        var compacta = sidebar.getAttribute('data-collapsed') === 'true';
        sidebar.setAttribute('data-collapsed', String(!compacta));
      });
    });
  }

  /* ---------- Navegação por abas ---------- */
  var META = {
    visao: ['Visão geral', 'Seu panorama financeiro, sem ruído.'],
    transacoes: ['Transações', 'Tudo o que entrou e saiu, agrupado por dia.'],
    assinaturas: ['Assinaturas', 'O que se repete todo mês, mesmo sem você lembrar.'],
    planejamento: ['Planejamento', 'O que você espera receber e pagar adiante.'],
    contas: ['Contas', 'Saldos calculados a partir das suas movimentações.'],
    cartoes: ['Cartões', 'Faturas e limite, em um lugar só.'],
    chat: ['Capí', 'Seu assistente financeiro.'],
    agentes: ['Agentes', 'Personagens temáticos para cada área da sua vida.']
  };

  var COM_PERIODO = ['visao', 'transacoes', 'assinaturas'];

  var titulo = document.querySelector('[data-page-title]');
  var subtitulo = document.querySelector('[data-page-sub]');
  var periodo = document.querySelector('[data-period]');

  function abrir(aba) {
    document.querySelectorAll('[data-page]').forEach(function (p) {
      p.hidden = p.dataset.page !== aba;
    });
    document.querySelectorAll('[data-goto]').forEach(function (b) {
      if (b.dataset.goto === aba) b.setAttribute('aria-current', 'page');
      else b.removeAttribute('aria-current');
    });
    if (titulo) titulo.textContent = META[aba][0];
    if (subtitulo) subtitulo.textContent = META[aba][1];
    if (periodo) periodo.hidden = COM_PERIODO.indexOf(aba) === -1;
    animarEntrada();
  }

  document.querySelectorAll('[data-goto]').forEach(function (b) {
    b.addEventListener('click', function () { abrir(b.dataset.goto); });
  });

  /* ---------- Período ---------- */
  var grupoPeriodo = document.querySelector('[data-period-group]');
  if (grupoPeriodo) {
    var intervalo = document.querySelector('[data-date-range]');
    var resumo = document.querySelector('[data-period-summary]');
    var RESUMOS = { '30d': '5 jul – 4 ago 2026', '12m': 'set 2025 – ago 2026' };

    grupoPeriodo.querySelectorAll('[data-period-value]').forEach(function (b) {
      b.addEventListener('click', function () {
        var v = b.dataset.periodValue;
        grupoPeriodo.querySelectorAll('[data-period-value]').forEach(function (o) {
          o.setAttribute('aria-pressed', String(o === b));
        });
        if (intervalo) intervalo.hidden = v !== 'intervalo';
        if (resumo) {
          resumo.hidden = v === 'intervalo';
          if (RESUMOS[v]) resumo.textContent = RESUMOS[v];
        }
      });
    });
  }

  /* ---------- Ocultar valores ---------- */
  var btnOlho = document.querySelector('[data-toggle-money]');
  if (btnOlho) {
    var oculto = false;
    btnOlho.addEventListener('click', function () {
      oculto = !oculto;
      document.querySelectorAll('[data-money]').forEach(function (el) {
        if (!el.dataset.moneyOriginal) el.dataset.moneyOriginal = el.textContent;
        el.textContent = oculto ? '••••••' : el.dataset.moneyOriginal;
      });
      var i = btnOlho.querySelector('i');
      if (i) i.className = oculto ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
      btnOlho.setAttribute('aria-pressed', String(oculto));
    });
  }

  /* ---------- Notificações ---------- */
  var sino = document.querySelector('[data-notif-toggle]');
  var painel = document.querySelector('[data-notif-panel]');
  if (sino && painel) {
    sino.addEventListener('click', function (e) {
      e.stopPropagation();
      painel.hidden = !painel.hidden;
      sino.setAttribute('aria-expanded', String(!painel.hidden));
    });
    document.addEventListener('click', function (e) {
      if (painel.hidden) return;
      if (painel.contains(e.target)) return;
      painel.hidden = true;
      sino.setAttribute('aria-expanded', 'false');
    });
    var marcar = document.querySelector('[data-notif-read]');
    if (marcar) {
      marcar.addEventListener('click', function () {
        painel.hidden = true;
        sino.setAttribute('aria-expanded', 'false');
        var ponto = sino.querySelector('.icon-btn__dot');
        if (ponto) ponto.hidden = true;
      });
    }
  }

  /* ---------- Animação de entrada dos cartões ---------- */
  var reduz = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function animarEntrada() {
    if (reduz) return;
    var pagina = document.querySelector('[data-page]:not([hidden])');
    if (!pagina) return;

    pagina.querySelectorAll('[data-enter]').forEach(function (el, i) {
      el.animate(
        [{ opacity: 0, transform: 'translateY(14px)' }, { opacity: 1, transform: 'none' }],
        { duration: 520, delay: i * 45, easing: 'cubic-bezier(0.22, 1, 0.36, 1)', fill: 'backwards' }
      );
    });

    pagina.querySelectorAll('.bar').forEach(function (el, i) {
      el.animate([{ transform: 'scaleY(0)' }, { transform: 'scaleY(1)' }],
        { duration: 700, delay: i * 40, easing: 'cubic-bezier(0.22, 1, 0.36, 1)', fill: 'backwards' });
    });

    pagina.querySelectorAll('.track__fill').forEach(function (el, i) {
      el.animate([{ transform: 'scaleX(0)' }, { transform: 'scaleX(1)' }],
        { duration: 640, delay: i * 55, easing: 'cubic-bezier(0.22, 1, 0.36, 1)', fill: 'backwards' });
    });
  }

  animarEntrada();
})();
