/* financiaí — comportamento da tela de autenticação.
   Sem dependências. Todo estado vive em atributos data-* lidos pelo CSS.
   Adaptado do pacote de handoff: os panes recuperar/redefinir/verificar são
   páginas próprias no backend (cada uma com sua rota), não abas trocadas via
   JS — por isso não há aqui a simulação de envio do protótipo estático. */

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
    if (!tabs || !COPY[modo]) return;
    tabs.setAttribute('data-active', modo);
    tabs.querySelectorAll('[data-tab]').forEach(function (btn) {
      btn.setAttribute('aria-selected', String(btn.dataset.tab === modo));
    });
    document.querySelectorAll('[data-pane]').forEach(function (form) {
      if (form.dataset.pane === 'login' || form.dataset.pane === 'registro') {
        form.setAttribute('data-state', form.dataset.pane === modo ? 'active' : 'hidden');
      }
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

/* ---------- Senha forte, recuperação e verificação ---------- */
(function () {
  var SIMBOLOS = /[!@#$%&*?_\-.]/;
  var PERMITIDOS = /^[A-Za-z0-9!@#$%&*?_\-.]*$/;
  var SEQUENCIAS = /(012|123|234|345|456|567|678|789|abc|bcd|cde|def|qwe|wer|ert|asd|sdf)/i;
  var REPETIDOS = /(.)\1{2,}/;
  var COMUNS = /^(senha|password|admin|123456|qwerty|financiai)/i;
  var CORES = ['#9AA3A0', '#C0392B', '#D68910', '#2E9E5B', '#137A4A'];
  var ROTULOS = ['', 'Fraca', 'Média', 'Forte', 'Excelente'];

  function analisar(senha) {
    var invalidos = [];
    senha.split('').forEach(function (c) {
      if (!PERMITIDOS.test(c) && invalidos.indexOf(c) === -1) invalidos.push(c);
    });
    var regras = {
      tamanho: senha.length >= 8,
      maiuscula: /[A-Z]/.test(senha),
      minuscula: /[a-z]/.test(senha),
      numero: /[0-9]/.test(senha),
      simbolo: SIMBOLOS.test(senha)
    };
    var ok = 0;
    Object.keys(regras).forEach(function (k) { if (regras[k]) ok++; });
    var limpa = invalidos.length === 0;
    var previsivel = SEQUENCIAS.test(senha) || REPETIDOS.test(senha) || COMUNS.test(senha);
    var nivel = 0;
    if (senha) {
      nivel = 1;
      if (ok >= 3) nivel = 2;
      if (ok === 5 && limpa) nivel = 3;
      if (ok === 5 && limpa && senha.length >= 12 && !previsivel) nivel = 4;
      if (previsivel && nivel > 2) nivel = 2;
    }
    return { regras: regras, invalidos: invalidos, previsivel: previsivel, nivel: nivel, valida: ok === 5 && limpa };
  }

  function ligar(prefixo) {
    var senha = document.getElementById(prefixo + 'Password');
    if (!senha) return;
    var confirma = document.getElementById(prefixo + 'Confirm');
    var msg = document.getElementById(prefixo + 'ConfirmMsg');
    var medidor = document.getElementById(prefixo + 'Meter');
    var regras = document.getElementById(prefixo + 'Rules');
    var erro = document.getElementById(prefixo + 'Error');
    var submit = document.getElementById(prefixo + 'Submit');

    function atualizar() {
      var v = senha.value;
      var a = analisar(v);
      var cor = CORES[a.nivel];
      var vazio = v.length === 0;

      medidor.hidden = vazio;
      regras.hidden = vazio;
      medidor.querySelectorAll('.pw-meter__bar').forEach(function (bar, i) {
        bar.style.background = a.nivel >= i + 1 ? cor : 'var(--line)';
      });
      var rotulo = medidor.querySelector('.pw-meter__label');
      rotulo.textContent = a.previsivel && a.nivel <= 2 ? 'Previsível' : ROTULOS[a.nivel];
      rotulo.style.color = cor;

      regras.querySelectorAll('.pw-rule').forEach(function (li) {
        var ok = a.regras[li.dataset.rule];
        li.classList.toggle('is-ok', !!ok);
        var mark = li.querySelector('.pw-rule__mark');
        mark.textContent = ok ? '✓' : '·';
        mark.style.background = ok ? cor : 'transparent';
        mark.style.borderColor = ok ? cor : 'var(--line)';
      });

      erro.hidden = a.invalidos.length === 0;
      if (a.invalidos.length) {
        erro.textContent = 'Caractere não permitido: ' + a.invalidos.map(function (c) {
          return c === ' ' ? 'espaço' : c;
        }).join(' ') + '. Use apenas letras, números e ! @ # $ % & * ? _ - .';
      }

      var igual = confirma.value.length > 0 && confirma.value === v;
      msg.hidden = confirma.value.length === 0;
      msg.textContent = igual ? 'As senhas conferem.' : 'As senhas não conferem.';
      msg.classList.toggle('is-ok', igual);
      msg.classList.toggle('is-bad', !igual);
      confirma.classList.toggle('is-ok', confirma.value.length > 0 && igual);
      confirma.classList.toggle('is-bad', confirma.value.length > 0 && !igual);

      var liberado = a.valida && igual;
      if (submit) {
        submit.disabled = !liberado;
        submit.setAttribute('aria-disabled', String(!liberado));
      }
    }

    senha.addEventListener('input', atualizar);
    confirma.addEventListener('input', atualizar);
    atualizar();
  }

  ligar('reg');
  ligar('rst');

  /* Recuperação de senha: só habilita o botão com um e-mail plausível. O
     envio em si é um POST real (rota password.email) — sem preventDefault
     nem simulação de resposta, diferente do protótipo estático. */
  var VALIDO = /^[^@\s]+@[^@\s]+\.[a-z]{2,}$/i;
  var email = document.getElementById('recEmail');
  var envio = document.getElementById('recSubmit');

  if (email && envio) {
    var validar = function () {
      var ok = VALIDO.test(email.value.trim());
      envio.disabled = !ok;
      envio.setAttribute('aria-disabled', String(!ok));
    };
    email.addEventListener('input', validar);
    validar();
  }

  document.querySelectorAll('[data-resend]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      btn.textContent = 'Enviando…';
      btn.disabled = true;
    });
  });
})();
