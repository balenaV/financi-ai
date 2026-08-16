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
    config: ['Configurações', 'Perfil, preferências e segurança da sua conta.'],
    novaConta: ['Nova conta manual', 'Uma conta que você controla por aqui, sem extrato.'],
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


  /* ---------- Dropdowns do sistema ---------- */
  function fecharDropdowns(exceto) {
    document.querySelectorAll('[data-dropdown]').forEach(function (d) {
      if (d === exceto) return;
      d.setAttribute('data-open', 'false');
      var menu = d.querySelector('.dropdown__menu');
      var btn = d.querySelector('[data-dropdown-btn]');
      if (menu) menu.hidden = true;
      if (btn) btn.setAttribute('aria-expanded', 'false');
    });
  }

  document.querySelectorAll('[data-dropdown]').forEach(function (drop) {
    var btn = drop.querySelector('[data-dropdown-btn]');
    var menu = drop.querySelector('.dropdown__menu');
    var rotulo = drop.querySelector('[data-dropdown-label]');
    if (!btn || !menu) return;

    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var aberto = drop.getAttribute('data-open') === 'true';
      fecharDropdowns(drop);
      drop.setAttribute('data-open', String(!aberto));
      menu.hidden = aberto;
      btn.setAttribute('aria-expanded', String(!aberto));
    });

    menu.querySelectorAll('.dropdown__opt').forEach(function (opt) {
      opt.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.querySelectorAll('.dropdown__opt').forEach(function (o) {
          o.classList.toggle('is-selected', o === opt);
        });
        if (rotulo) rotulo.textContent = opt.textContent.trim();
        drop.setAttribute('data-open', 'false');
        menu.hidden = true;
        btn.setAttribute('aria-expanded', 'false');
      });
    });
  });

  document.addEventListener('click', function () { fecharDropdowns(null); });

  /* ---------- Modais (cartão / assinatura) ---------- */
  function abrirModal(nome) {
    var modal = document.querySelector('[data-modal="' + nome + '"]');
    if (!modal) return;
    modal.hidden = false;
    var foco = modal.querySelector('input, button');
    if (foco) foco.focus();
  }

  function fecharModais() {
    document.querySelectorAll('[data-modal]').forEach(function (m) { m.hidden = true; });
  }

  document.querySelectorAll('[data-modal-open]').forEach(function (b) {
    b.addEventListener('click', function () { abrirModal(b.dataset.modalOpen); });
  });

  document.querySelectorAll('[data-modal-close]').forEach(function (b) {
    b.addEventListener('click', fecharModais);
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') fecharModais();
  });

  /* ---------- Grupos de chips (bandeira, ciclo) ---------- */
  document.querySelectorAll('[data-chip-group]').forEach(function (grupo) {
    grupo.querySelectorAll('.chip').forEach(function (chip) {
      chip.addEventListener('click', function () {
        grupo.querySelectorAll('.chip').forEach(function (c) {
          c.classList.toggle('is-selected', c === chip);
        });
      });
    });
  });

  /* ---------- Nova conta manual: seleções + prévia ---------- */
  var previaIcone = document.querySelector('[data-preview-icon]');
  var previaNome = document.querySelector('[data-preview-name]');
  var previaTipo = document.querySelector('[data-preview-type]');
  var previaSaldo = document.querySelector('[data-preview-balance]');
  var previaNota = document.querySelector('[data-preview-note]');
  var campoNome = document.querySelector('[data-account-name]');
  var campoBanco = document.querySelector('[data-account-bank]');
  var campoSaldo = document.querySelector('[data-account-balance]');
  var campoData = document.querySelector('[data-account-date]');
  var grupoTipo = document.querySelector('[data-account-type]');
  var grupoCores = document.querySelector('[data-account-colors]');
  var switchTotal = document.querySelector('[data-account-total]');

  function atualizarPrevia() {
    if (!previaNome) return;
    var tipoSel = grupoTipo && grupoTipo.querySelector('.option-tile.is-selected');
    var tipoNome = tipoSel ? tipoSel.textContent.trim() : 'Conta corrente';
    var banco = campoBanco && campoBanco.value.trim();
    var num = parseFloat(String(campoSaldo ? campoSaldo.value : '').replace(/\./g, '').replace(',', '.'));
    var partes = String(campoData ? campoData.value : '').split('-');
    var somaTotal = !switchTotal || switchTotal.classList.contains('is-on');

    previaNome.textContent = (campoNome && campoNome.value.trim()) || 'Nome da conta';
    previaTipo.textContent = banco ? tipoNome + ' · ' + banco : tipoNome;
    previaSaldo.textContent = 'R$ ' + (isNaN(num) ? 0 : num).toLocaleString('pt-BR', {
      minimumFractionDigits: 2, maximumFractionDigits: 2
    });
    if (partes.length === 3) {
      previaNota.textContent = 'Saldo informado em ' + partes[2] + '/' + partes[1] + '/' + partes[0] +
        (somaTotal ? '' : ' · fora do saldo total');
    }
  }

  [campoNome, campoBanco, campoSaldo, campoData].forEach(function (el) {
    if (el) el.addEventListener('input', atualizarPrevia);
  });

  if (grupoTipo) {
    grupoTipo.querySelectorAll('.option-tile').forEach(function (tile) {
      tile.addEventListener('click', function () {
        grupoTipo.querySelectorAll('.option-tile').forEach(function (t) {
          t.classList.toggle('is-selected', t === tile);
        });
        if (previaIcone) previaIcone.innerHTML = '<i class="' + tile.dataset.icon + '"></i>';
        atualizarPrevia();
      });
    });
  }

  if (grupoCores) {
    grupoCores.querySelectorAll('.swatch').forEach(function (sw) {
      sw.addEventListener('click', function () {
        grupoCores.querySelectorAll('.swatch').forEach(function (o) {
          o.classList.toggle('is-selected', o === sw);
        });
        if (previaIcone) previaIcone.style.background = sw.dataset.value;
      });
    });
  }

  if (switchTotal) {
    switchTotal.addEventListener('click', function () {
      var ligado = switchTotal.classList.toggle('is-on');
      switchTotal.setAttribute('aria-checked', String(ligado));
      atualizarPrevia();
    });
  }

  atualizarPrevia();


  /* ---------- Date picker do sistema ---------- */
  var MESES_DP = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho',
    'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
  var SEMANA_DP = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb'];

  function fecharDatepickers(exceto) {
    document.querySelectorAll('[data-datepicker]').forEach(function (dp) {
      if (dp === exceto) return;
      dp.setAttribute('data-open', 'false');
      var painel = dp.querySelector('[data-datepicker-panel]');
      var btn = dp.querySelector('[data-datepicker-btn]');
      if (painel) painel.hidden = true;
      if (btn) btn.setAttribute('aria-expanded', 'false');
    });
  }

  document.querySelectorAll('[data-datepicker]').forEach(function (dp) {
    var btn = dp.querySelector('[data-datepicker-btn]');
    var painel = dp.querySelector('[data-datepicker-panel]');
    var rotulo = dp.querySelector('[data-datepicker-label]');
    var oculto = dp.parentNode && dp.parentNode.querySelector('input[type="hidden"]');
    if (!btn || !painel) return;

    var partes = (dp.dataset.value || '').split('-');
    var sel = new Date(Number(partes[0]), Number(partes[1]) - 1, Number(partes[2]));
    var visto = new Date(sel.getFullYear(), sel.getMonth(), 1);

    function iso(d) {
      return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') +
        '-' + String(d.getDate()).padStart(2, '0');
    }

    function br(d) {
      return String(d.getDate()).padStart(2, '0') + '/' +
        String(d.getMonth() + 1).padStart(2, '0') + '/' + d.getFullYear();
    }

    function desenhar() {
      var inicio = visto.getDay();
      var total = new Date(visto.getFullYear(), visto.getMonth() + 1, 0).getDate();
      var html = '<div class="datepicker__head">' +
        '<button class="datepicker__nav" type="button" data-dp-prev aria-label="Mês anterior"><i class="fa-solid fa-chevron-left"></i></button>' +
        '<span class="datepicker__month">' + MESES_DP[visto.getMonth()] + ' ' + visto.getFullYear() + '</span>' +
        '<button class="datepicker__nav" type="button" data-dp-next aria-label="Próximo mês"><i class="fa-solid fa-chevron-right"></i></button>' +
        '</div><div class="datepicker__week">';
      SEMANA_DP.forEach(function (s) { html += '<span>' + s + '</span>'; });
      html += '</div><div class="datepicker__grid">';
      for (var i = 0; i < 42; i++) {
        var n = i - inicio + 1;
        if (n < 1 || n > total) {
          html += '<button class="datepicker__day datepicker__day--empty" type="button" tabindex="-1"></button>';
          continue;
        }
        var escolhido = n === sel.getDate() && visto.getMonth() === sel.getMonth() &&
          visto.getFullYear() === sel.getFullYear();
        html += '<button class="datepicker__day' + (escolhido ? ' is-selected' : '') +
          '" type="button" data-dp-day="' + n + '">' + n + '</button>';
      }
      painel.innerHTML = html + '</div>';
    }

    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var aberto = dp.getAttribute('data-open') === 'true';
      fecharDropdowns(null);
      fecharDatepickers(dp);
      if (!aberto) desenhar();
      dp.setAttribute('data-open', String(!aberto));
      painel.hidden = aberto;
      btn.setAttribute('aria-expanded', String(!aberto));
    });

    painel.addEventListener('click', function (e) {
      e.stopPropagation();
      var alvo = e.target.closest('button');
      if (!alvo) return;
      if (alvo.hasAttribute('data-dp-prev')) {
        visto = new Date(visto.getFullYear(), visto.getMonth() - 1, 1);
        desenhar();
      } else if (alvo.hasAttribute('data-dp-next')) {
        visto = new Date(visto.getFullYear(), visto.getMonth() + 1, 1);
        desenhar();
      } else if (alvo.hasAttribute('data-dp-day')) {
        sel = new Date(visto.getFullYear(), visto.getMonth(), Number(alvo.dataset.dpDay));
        dp.dataset.value = iso(sel);
        if (rotulo) rotulo.textContent = br(sel);
        if (oculto) {
          oculto.value = iso(sel);
          oculto.dispatchEvent(new Event('input', { bubbles: true }));
        }
        dp.setAttribute('data-open', 'false');
        painel.hidden = true;
        btn.setAttribute('aria-expanded', 'false');
      }
    });
  });

  document.addEventListener('click', function () { fecharDatepickers(null); });


  /* ---------- Cartão do usuário ---------- */
  var userMenu = document.querySelector('[data-user-menu]');
  if (userMenu) {
    var userBtn = userMenu.querySelector('[data-user-toggle]');
    var userCard = userMenu.querySelector('[data-user-card]');

    userBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      var aberto = userMenu.getAttribute('data-open') === 'true';
      userMenu.setAttribute('data-open', String(!aberto));
      userCard.hidden = aberto;
      userBtn.setAttribute('aria-expanded', String(!aberto));
    });

    userCard.addEventListener('click', function (e) { e.stopPropagation(); });

    document.addEventListener('click', function () {
      userMenu.setAttribute('data-open', 'false');
      userCard.hidden = true;
      userBtn.setAttribute('aria-expanded', 'false');
    });
  }

  /* ---------- Tema pelas preferências ---------- */
  document.querySelectorAll('[data-theme-set]').forEach(function (b) {
    b.addEventListener('click', function () {
      document.documentElement.setAttribute('data-theme', b.dataset.themeSet);
      try { localStorage.setItem('financiai-theme', b.dataset.themeSet); } catch (err) {}
    });
  });

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


/* ---- Upload de foto de perfil (Configurações › Perfil) ---- */
(function () {
  var zona = document.getElementById('avatarUpload');
  if (!zona) return;
  var input = document.getElementById('avatarInput');
  var img = document.getElementById('avatarPreview');
  var iniciais = zona.querySelector('.avatar-upload__initials');
  var nome = document.getElementById('avatarName');
  var erro = document.getElementById('avatarError');
  var remover = document.getElementById('avatarRemove');

  function falhar(msg) { erro.textContent = msg; erro.hidden = false; }

  function ler(arquivo) {
    if (!arquivo) return;
    if (!/^image\/(png|jpeg)$/.test(arquivo.type)) return falhar('Use um arquivo JPG ou PNG.');
    if (arquivo.size > 5 * 1024 * 1024) return falhar('A imagem passa de 5 MB. Escolha uma menor.');
    var leitor = new FileReader();
    leitor.onload = function () {
      img.src = leitor.result; img.hidden = false;
      iniciais.hidden = true; remover.hidden = false;
      nome.textContent = arquivo.name; erro.hidden = true;
    };
    leitor.readAsDataURL(arquivo);
  }

  document.getElementById('avatarPick').addEventListener('click', function () { input.click(); });
  input.addEventListener('change', function (e) { ler(e.target.files && e.target.files[0]); });
  remover.addEventListener('click', function () {
    input.value = ''; img.removeAttribute('src'); img.hidden = true;
    iniciais.hidden = false; remover.hidden = true;
    nome.textContent = 'Nenhuma foto enviada'; erro.hidden = true;
  });
  zona.addEventListener('dragover', function (e) { e.preventDefault(); zona.classList.add('is-dragging'); });
  zona.addEventListener('dragleave', function () { zona.classList.remove('is-dragging'); });
  zona.addEventListener('drop', function (e) {
    e.preventDefault(); zona.classList.remove('is-dragging');
    ler(e.dataTransfer.files && e.dataTransfer.files[0]);
  });
})();

/* ======================================================================
   Menus de três pontos (contas, cartões, transações...) — genérico.
   ====================================================================== */
(function () {
  document.addEventListener('click', function (ev) {
    var btn = ev.target.closest('[data-menu-btn]');
    document.querySelectorAll('[data-menu]').forEach(function (m) {
      var aberto = btn && m.contains(btn) && m.querySelector('[data-menu-list]').hidden;
      m.querySelector('[data-menu-list]').hidden = !aberto;
      m.querySelector('[data-menu-btn]').setAttribute('aria-expanded', String(!!aberto));
      m.style.zIndex = aberto ? '30' : '';
    });
  });
})();

/* ======================================================================
   Contas — histórico e lista de arquivadas.
   Diferente do protótipo estático: os dados já vêm renderizados pelo
   servidor (um painel por conta, o histórico já calculado). O JS só
   decide qual painel mostrar — não busca nem monta HTML.
   ====================================================================== */
(function () {
  document.addEventListener('click', function (ev) {
    var ver = ev.target.closest('[data-account-history]');
    if (ver) {
      var id = ver.dataset.accountHistory;
      document.querySelectorAll('[data-account-panel]').forEach(function (p) {
        p.hidden = p.dataset.accountPanel !== id;
      });
      document.querySelectorAll('[data-account]').forEach(function (card) {
        card.classList.toggle('is-selected', card.dataset.account === id);
      });
      var painel = document.querySelector('[data-account-panel="' + id + '"]');
      if (painel) painel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    if (ev.target.closest('[data-account-close]')) {
      document.querySelectorAll('[data-account-panel]').forEach(function (p) { p.hidden = true; });
      document.querySelectorAll('[data-account]').forEach(function (card) { card.classList.remove('is-selected'); });
    }
  });

  var alternar = document.querySelector('[data-archived-toggle]');
  var lista = document.querySelector('[data-archived-list]');
  if (alternar && lista) {
    alternar.addEventListener('click', function () {
      lista.hidden = !lista.hidden;
      alternar.textContent = lista.hidden ? 'Mostrar' : 'Ocultar';
    });
  }
})();

/* ======================================================================
   Cartões — faturas mensais e pagamento.
   Cada fatura já vem renderizada como um "slide" oculto dentro do painel
   do cartão (data-bill-slide); navegar entre meses é só trocar qual
   slide está visível e copiar os dados dele para o cabeçalho comum.
   ====================================================================== */
(function () {
  var paineis = document.querySelectorAll('[data-invoices-panel]');
  if (!paineis.length) return;

  function painelDoCartao(id) {
    return document.querySelector('[data-invoices-panel="' + id + '"]');
  }

  function slideVisivel(painel) {
    return painel.querySelector('[data-bill-slide]:not([hidden])');
  }

  function moeda(v) {
    return 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function sincronizarCabecalho(painel) {
    var slide = slideVisivel(painel);
    var todos = painel.querySelectorAll('[data-bill-slide]');
    var indice = slide ? Array.prototype.indexOf.call(todos, slide) : -1;

    var mes = painel.querySelector('[data-invoice-month]');
    var due = painel.querySelector('[data-invoice-due]');
    var count = painel.querySelector('[data-invoice-count]');
    var total = painel.querySelector('[data-invoice-total]');
    var estado = painel.querySelector('[data-invoice-state]');
    var pagar = painel.querySelector('[data-invoice-pay]');
    var prev = painel.querySelector('[data-invoice-prev]');
    var next = painel.querySelector('[data-invoice-next]');

    if (!slide) {
      if (mes) mes.textContent = '—';
      return;
    }

    if (mes) mes.textContent = slide.dataset.month;
    if (due) due.textContent = slide.dataset.due;
    if (count) count.textContent = slide.dataset.count;
    var paga = slide.dataset.paid === '1';
    if (total) {
      total.dataset.moneyValor = moeda(slide.dataset.total);
      total.textContent = window.__valoresOcultos ? '••••••' : moeda(slide.dataset.total);
    }
    if (estado) {
      estado.classList.toggle('is-paid', paga);
      estado.innerHTML = paga ? '<i class="fa-solid fa-check" aria-hidden="true"></i>Paga' : '<i class="fa-regular fa-clock" aria-hidden="true"></i>Em aberto';
    }
    if (pagar) pagar.hidden = paga;
    if (prev) prev.style.opacity = indice >= todos.length - 1 ? '0.4' : '';
    if (next) next.style.opacity = indice <= 0 ? '0.4' : '';

    if (window.aplicarOcultarValores) window.aplicarOcultarValores();
  }

  function abrirPainel(cardId) {
    paineis.forEach(function (p) { p.hidden = p.dataset.invoicesPanel !== cardId; });
    var painel = painelDoCartao(cardId);
    if (!painel) return null;
    var slides = painel.querySelectorAll('[data-bill-slide]');
    slides.forEach(function (s, i) { s.hidden = i !== 0; });
    document.querySelectorAll('[data-card]').forEach(function (card) {
      card.classList.toggle('is-selected', card.dataset.card === cardId);
    });
    sincronizarCabecalho(painel);
    return painel;
  }

  function abrirPagamento(painel) {
    var slide = slideVisivel(painel);
    var modal = document.querySelector('[data-modal="pagamento"]');
    if (!slide || !modal) return;
    var tituloEl = painel.querySelector('.invoices__title');
    var titulo = tituloEl ? tituloEl.textContent : '';
    var form = modal.querySelector('[data-pay-form]');
    if (form) form.action = slide.dataset.payUrl;
    var card = modal.querySelector('[data-pay-card]');
    if (card) card.textContent = titulo.replace(/^Faturas · /, '') + ' · ' + slide.dataset.month;
    var due = modal.querySelector('[data-pay-due]');
    if (due) due.textContent = slide.dataset.due;
    var valor = modal.querySelector('[data-pay-amount]');
    if (valor) valor.value = Number(slide.dataset.total).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    modal.hidden = false;
  }

  document.addEventListener('click', function (ev) {
    var ver = ev.target.closest('[data-card-invoices]');
    if (ver) abrirPainel(ver.dataset.cardInvoices);

    var pagarCartao = ev.target.closest('[data-card-pay]');
    if (pagarCartao) {
      var painelAberto = abrirPainel(pagarCartao.dataset.cardPay);
      if (painelAberto) abrirPagamento(painelAberto);
    }

    var fechar = ev.target.closest('[data-invoice-close]');
    if (fechar) {
      var painelAtual = fechar.closest('[data-invoices-panel]');
      if (painelAtual) painelAtual.hidden = true;
      document.querySelectorAll('[data-card]').forEach(function (card) { card.classList.remove('is-selected'); });
    }

    var pagarFatura = ev.target.closest('[data-invoice-pay]');
    if (pagarFatura) {
      var painelDaFatura = pagarFatura.closest('[data-invoices-panel]');
      if (painelDaFatura) abrirPagamento(painelDaFatura);
    }

    var anterior = ev.target.closest('[data-invoice-prev]');
    var proxima = ev.target.closest('[data-invoice-next]');
    if (anterior || proxima) {
      var painelNav = (anterior || proxima).closest('[data-invoices-panel]');
      if (!painelNav) return;
      var slides = painelNav.querySelectorAll('[data-bill-slide]');
      var atualIdx = Array.prototype.indexOf.call(slides, slideVisivel(painelNav));
      if (atualIdx === -1) return;
      var novoIdx = anterior ? Math.min(slides.length - 1, atualIdx + 1) : Math.max(0, atualIdx - 1);
      if (novoIdx === atualIdx) return;
      slides[atualIdx].hidden = true;
      slides[novoIdx].hidden = false;
      sincronizarCabecalho(painelNav);
    }
  });
})();
