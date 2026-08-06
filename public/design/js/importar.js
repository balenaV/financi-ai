/* financiaí — fluxo de importação de extrato.
   Os tutoriais por banco são dados: no backend virão de uma tabela/config. */

(function () {

  var TUTORIAIS = {
    'Nubank': {
      passos: [
        'Abra o app do Nubank e toque em Conta, na tela inicial.',
        'Toque em Pedir extrato (ou Exportar extrato).',
        'Selecione o período desejado e confirme.',
        'O extrato chega no seu e-mail cadastrado em PDF, OFX e CSV. Baixe o OFX e traga para cá.'
      ],
      obs: 'O Nubank envia por e-mail, não baixa direto no app. Confira se o e-mail cadastrado está atualizado.'
    },
    'Itaú': {
      passos: [
        'Acesse o Internet Banking pelo computador.',
        'Vá em Conta corrente → Consultar extrato por período.',
        'Escolha o intervalo de datas e clique em Continuar.',
        'Selecione Salvar em outros formatos e escolha OFX / Money 2000.'
      ],
      obs: 'A exportação em OFX está disponível no Internet Banking, não no app.'
    },
    'Bradesco': {
      passos: [
        'Acesse o Internet Banking e vá em Menu → Saldos e Extratos → Extrato Mensal / Por Período.',
        'Selecione o período e a conta desejada e clique em Buscar.',
        'Com o extrato na tela, clique em Salvar como arquivo.',
        'Escolha OFX (Money 2000 em diante) e salve no computador.'
      ],
      obs: 'O Bradesco costuma disponibilizar apenas os últimos 60 dias. Exporte com frequência para não perder histórico.'
    },
    'Banco do Brasil': {
      passos: [
        'Acesse o Internet Banking e vá em Menu → Conta corrente → Extrato.',
        'Selecione o período desejado.',
        'Clique no ícone de download, na parte superior da tela.',
        'Escolha Money 2000+ (OFX) e salve o arquivo.'
      ],
      obs: 'O BB disponibiliza apenas os últimos 60 dias de extrato.'
    },
    'Caixa': {
      passos: [
        'Acesse o Internet Banking da Caixa e abra o menu ☰, no canto superior esquerdo.',
        'Na seção Extratos, escolha Conta por Período.',
        'Selecione o período desejado e clique em Gerar Arquivo para Gerenciadores Financeiros.',
        'Marque a opção OFX e clique em Continuar para baixar.'
      ],
      obs: 'A Caixa também limita o extrato aos últimos 60 dias.'
    },
    'Santander': {
      passos: [
        'Entre no Internet Banking e vá em Menu → Conta corrente → Extrato (Money).',
        'Selecione o período e clique em Exibir.',
        'No extrato, clique em Exportar.',
        'Escolha Money 2000 ou superior e salve o arquivo.'
      ],
      obs: ''
    },
    'Inter': {
      passos: [
        'Acesse o Internet Banking do Inter pelo navegador (o login pede o celular).',
        'No menu, entre em Conta Digital → Extrato.',
        'Selecione o período e clique em Aplicar.',
        'Clique em Exportar, no canto superior direito, e escolha OFX.'
      ],
      obs: 'A exportação em OFX fica no Internet Banking, não no app.'
    },
    'C6 Bank': {
      passos: [
        'Abra o app do C6 Bank e toque em Exibir extrato.',
        'Toque em Exportar extrato.',
        'Escolha um dos períodos sugeridos ou personalize as datas.',
        'Siga as instruções para receber o arquivo.'
      ],
      obs: 'O intervalo selecionado precisa estar dentro de 365 dias.'
    },
    'Outro banco': {
      passos: [
        'Abra o extrato da sua conta no app ou no site do banco.',
        'Procure por Exportar, Compartilhar ou Salvar em arquivo.',
        'Prefira OFX quando existir — a importação fica mais precisa. CSV também funciona.',
        'Baixe o arquivo e envie na etapa anterior.'
      ],
      obs: 'Se o seu banco não exporta OFX, o CSV resolve: você indica as colunas na etapa seguinte.'
    }
  };

  var ORDEM = ['upload', 'mapear', 'revisar', 'concluido'];
  var etapaAtual = 'upload';
  var temArquivo = false;

  /* ---------- Navegação entre etapas ---------- */
  function irPara(etapa) {
    etapaAtual = etapa;
    var idx = ORDEM.indexOf(etapa);

    document.querySelectorAll('[data-step]').forEach(function (s) {
      s.hidden = s.dataset.step !== etapa;
    });

    document.querySelectorAll('[data-trail]').forEach(function (li) {
      var i = ORDEM.indexOf(li.dataset.trail);
      var estado = i < idx ? 'done' : (i === idx ? 'current' : 'todo');
      li.setAttribute('data-state', estado);
      var passo = li.querySelector('.trail-step');
      passo.setAttribute('data-state', estado);
      var icone = passo.querySelector('.trail-step__bubble i');
      if (icone) icone.className = estado === 'done' ? 'fa-solid fa-check' : li.dataset.trailIcon;
    });

    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  document.querySelectorAll('[data-step-goto]').forEach(function (b) {
    b.addEventListener('click', function () {
      if (b.dataset.stepGoto === 'mapear' && !temArquivo) return;
      irPara(b.dataset.stepGoto);
    });
  });

  /* ---------- Etapa 1: seleção do arquivo ---------- */
  var zona = document.querySelector('[data-dropzone]');
  var chip = document.querySelector('[data-file-chip]');
  var chipNome = document.querySelector('[data-file-name]');
  var btnContinuar = document.querySelector('[data-continue]');
  var input = document.querySelector('[data-file-input]');

  function definirArquivo(nome) {
    temArquivo = !!nome;
    if (chip) chip.hidden = !temArquivo;
    if (chipNome && nome) chipNome.textContent = nome;
    if (btnContinuar) btnContinuar.disabled = !temArquivo;
  }

  if (zona) {
    zona.addEventListener('dragover', function (e) {
      e.preventDefault();
      zona.setAttribute('data-dragging', 'true');
    });
    zona.addEventListener('dragleave', function () {
      zona.setAttribute('data-dragging', 'false');
    });
    zona.addEventListener('drop', function (e) {
      e.preventDefault();
      zona.setAttribute('data-dragging', 'false');
      var f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
      definirArquivo(f ? f.name : 'extrato-agosto-2026.ofx');
    });
  }

  if (input) {
    input.addEventListener('change', function () {
      var f = input.files && input.files[0];
      definirArquivo(f ? f.name : 'extrato-agosto-2026.ofx');
    });
  }

  var remover = document.querySelector('[data-file-remove]');
  if (remover) {
    remover.addEventListener('click', function (e) {
      e.preventDefault();
      if (input) input.value = '';
      definirArquivo(null);
    });
  }

  /* ---------- Tutorial por banco ---------- */
  var seletorBanco = document.querySelector('[data-bank-select]');
  var listaPassos = document.querySelector('[data-bank-steps]');
  var obsBanco = document.querySelector('[data-bank-note]');

  function renderTutorial(banco) {
    var t = TUTORIAIS[banco] || TUTORIAIS['Outro banco'];
    if (listaPassos) {
      listaPassos.textContent = '';
      t.passos.forEach(function (texto, i) {
        var li = document.createElement('li');
        var num = document.createElement('span');
        num.className = 'tutorial__num';
        num.textContent = String(i + 1);
        var txt = document.createElement('span');
        txt.className = 'tutorial__text';
        txt.textContent = texto;
        li.appendChild(num);
        li.appendChild(txt);
        listaPassos.appendChild(li);
      });
    }
    if (obsBanco) {
      obsBanco.hidden = !t.obs;
      var alvo = obsBanco.querySelector('[data-bank-note-text]');
      if (alvo) alvo.textContent = t.obs;
    }
  }

  if (seletorBanco) {
    seletorBanco.addEventListener('change', function () { renderTutorial(seletorBanco.value); });
    renderTutorial(seletorBanco.value);
  }

  /* ---------- Etapa 3: filtros e seleção ---------- */
  var filtros = document.querySelector('[data-review-filters]');
  var status = document.querySelector('[data-review-status]');
  var confirmar = document.querySelector('[data-confirm]');
  var entradas = Array.prototype.slice.call(document.querySelectorAll('[data-entry]'));
  var total = entradas.length;

  function incluidas() {
    return entradas.filter(function (e) {
      return e.querySelector('.entry__check').checked;
    }).length;
  }

  function atualizarContagem() {
    var n = incluidas();
    if (status) status.textContent = n + ' de ' + total + ' serão importados';
    if (confirmar) {
      var rotulo = confirmar.querySelector('[data-confirm-label]');
      if (rotulo) rotulo.textContent = 'Importar ' + n + ' lançamentos';
    }
  }

  entradas.forEach(function (e) {
    e.querySelector('.entry__check').addEventListener('change', atualizarContagem);
  });

  var marcarTodos = document.querySelector('[data-check-all]');
  if (marcarTodos) {
    marcarTodos.addEventListener('change', function () {
      entradas.forEach(function (e) {
        if (e.dataset.duplicate === 'true') return;
        e.querySelector('.entry__check').checked = marcarTodos.checked;
      });
      atualizarContagem();
    });
  }

  if (filtros) {
    filtros.querySelectorAll('[data-filter]').forEach(function (b) {
      b.addEventListener('click', function () {
        var f = b.dataset.filter;
        filtros.querySelectorAll('[data-filter]').forEach(function (o) {
          o.setAttribute('aria-pressed', String(o === b));
        });
        entradas.forEach(function (e) {
          e.hidden = f !== 'todos' && e.dataset.state !== f;
        });
      });
    });
  }

  atualizarContagem();

  /* ---------- Recomeçar ---------- */
  var recomecar = document.querySelector('[data-restart]');
  if (recomecar) {
    recomecar.addEventListener('click', function () {
      definirArquivo(null);
      irPara('upload');
    });
  }

  definirArquivo(null);
  irPara('upload');
})();
