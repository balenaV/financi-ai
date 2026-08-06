/* financiaí — comportamento da landing page.
   Depende de theme.js apenas para o alternador de tema. */

(function () {

  /* ---------- Header que encolhe ao rolar ---------- */
  var header = document.querySelector('[data-header]');
  if (header) {
    var raf = null;
    var apply = function () {
      header.setAttribute('data-floating', String(window.scrollY > 24));
    };
    apply();
    window.addEventListener('scroll', function () {
      if (raf) return;
      raf = requestAnimationFrame(function () { raf = null; apply(); });
    }, { passive: true });
  }

  /* ---------- Menu mobile ---------- */
  var burger = document.querySelector('[data-burger]');
  var menu = document.querySelector('[data-mobile-menu]');
  if (burger && menu) {
    burger.addEventListener('click', function () {
      menu.hidden = !menu.hidden;
      burger.setAttribute('aria-expanded', String(!menu.hidden));
    });
    menu.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () {
        menu.hidden = true;
        burger.setAttribute('aria-expanded', 'false');
      });
    });
    window.addEventListener('resize', function () {
      if (window.innerWidth >= 940) menu.hidden = true;
    });
  }

  /* ---------- Revelação ao entrar na viewport ---------- */
  var reveals = document.querySelectorAll('[data-reveal]');
  var reduz = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reveals.length && !reduz && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        e.target.setAttribute('data-revealed', 'true');
        io.unobserve(e.target);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

    reveals.forEach(function (n) {
      /* Elementos já visíveis no primeiro paint não são escondidos. */
      if (n.getBoundingClientRect().top < window.innerHeight * 0.92) return;
      n.setAttribute('data-revealed', 'false');
      io.observe(n);
    });
  }

  /* ---------- Preço com dígitos rolantes ---------- */
  function montarPreco(el, texto) {
    var atual = el.getAttribute('data-value');
    if (atual === texto) return;

    if (!atual) {
      el.classList.add('rolling');
      el.textContent = '';
      var idx = 0;
      texto.split('').forEach(function (ch) {
        if (!/[0-9]/.test(ch)) {
          var s = document.createElement('span');
          s.className = 'rolling__char';
          s.textContent = ch === ' ' ? '\u00A0' : ch;
          el.appendChild(s);
          return;
        }
        var wrap = document.createElement('span');
        wrap.className = 'rolling__digit';
        var reel = document.createElement('span');
        reel.className = 'rolling__reel';
        reel.style.transitionDelay = (idx * 45) + 'ms';
        for (var n = 0; n <= 9; n++) {
          var d = document.createElement('span');
          d.textContent = String(n);
          reel.appendChild(d);
        }
        wrap.appendChild(reel);
        el.appendChild(wrap);
        idx++;
      });
    }

    /* Posiciona cada bobina no dígito correspondente. */
    var reels = el.querySelectorAll('.rolling__reel');
    var i = 0;
    texto.split('').forEach(function (ch) {
      if (!/[0-9]/.test(ch)) return;
      if (reels[i]) reels[i].style.transform = 'translateY(' + (-Number(ch) * 10) + '%)';
      i++;
    });

    el.setAttribute('data-value', texto);
  }

  /* ---------- Alternador mensal / anual ---------- */
  var toggle = document.querySelector('[data-cycle-toggle]');
  if (toggle) {
    var pill = toggle.querySelector('.cycle-toggle__pill');
    var botoes = toggle.querySelectorAll('[data-cycle]');

    var PRECOS = {
      mensal: { essencial: 'R$ 19,90', completo: 'R$ 35,90', nota: 'cobrado mensalmente · valores de referência do beta' },
      anual: { essencial: 'R$ 13,90', completo: 'R$ 24,90', nota: 'cobrado anualmente · valores de referência do beta' }
    };

    var medir = function () {
      var ativo = toggle.querySelector('[aria-pressed="true"]');
      if (!ativo || !pill) return;
      pill.style.width = ativo.offsetWidth + 'px';
      pill.style.transform = 'translateX(' + (ativo.offsetLeft - 5) + 'px)';
    };

    var setCiclo = function (ciclo) {
      botoes.forEach(function (b) {
        b.setAttribute('aria-pressed', String(b.dataset.cycle === ciclo));
      });
      var p = PRECOS[ciclo];
      var essencial = document.querySelector('[data-price="essencial"]');
      var completo = document.querySelector('[data-price="completo"]');
      if (essencial) montarPreco(essencial, p.essencial);
      if (completo) montarPreco(completo, p.completo);
      document.querySelectorAll('[data-cycle-note]').forEach(function (n) {
        n.textContent = p.nota;
      });
      medir();
    };

    botoes.forEach(function (b) {
      b.addEventListener('click', function () { setCiclo(b.dataset.cycle); });
    });

    setCiclo('mensal');
    window.addEventListener('resize', medir);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(medir);
  }

})();
