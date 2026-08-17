/* financiaí — fluxo real de importação de extrato, usando o markup e as
   classes do pacote de design (steps-trail, dropzone, entry, done-hero...).
   Não é uma cópia do js/importar.js do pacote: aquele é uma demonstração
   100% client-side (dados falsos, sem upload de verdade); aqui o upload,
   a leitura do arquivo e a confirmação passam pelas rotas reais do backend. */

(function () {
  var root = document.getElementById('import-wizard');
  if (!root) return;

  var storeUrl = root.getAttribute('data-store-url');
  var transactionsUrl = root.getAttribute('data-transactions-url');
  var csrfMeta = document.querySelector('meta[name="csrf-token"]');
  var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

  function postJson(url, body) {
    var headers = { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' };
    if (typeof body === 'string') headers['Content-Type'] = 'application/json';

    return fetch(url, { method: 'POST', headers: headers, body: body }).then(function (response) {
      return response.json().then(function (data) {
        if (!response.ok) throw new Error(data.message || 'Falha na requisição.');

        return data;
      });
    });
  }

  function getJson(url) {
    return fetch(url, { headers: { Accept: 'application/json' } }).then(function (response) {
      return response.json();
    });
  }

  function formatMoney(amount) {
    return 'R$ ' + Number(amount).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  /* ---------- Dropdown do sistema, montado via JS ----------
     dashboard.js só liga abrir/fechar nos ".dropdown" que já existem quando a
     página carrega; estes aqui nascem depois (mapeamento de colunas do CSV
     muda por arquivo). Replica aqui a mesma lógica de abrir/fechar e marcar
     .is-selected — fechar-ao-clicar-fora já funciona sozinho porque
     dashboard.js reconsulta o DOM a cada clique, sem depender de uma lista
     fixada no carregamento da página. */
  function createDropdown(options, selectedValue, hiddenAttr) {
    var wrap = document.createElement('div');
    wrap.className = 'dropdown dropdown--block';
    wrap.setAttribute('data-dropdown', '');

    var btn = document.createElement('button');
    btn.className = 'dropdown__btn';
    btn.type = 'button';
    btn.setAttribute('aria-haspopup', 'listbox');
    btn.setAttribute('aria-expanded', 'false');
    btn.setAttribute('data-dropdown-btn', '');

    var rotulo = document.createElement('span');
    rotulo.setAttribute('data-dropdown-label', '');

    var chevron = document.createElement('i');
    chevron.className = 'fa-solid fa-chevron-down dropdown__chevron';

    btn.appendChild(rotulo);
    btn.appendChild(chevron);

    var menu = document.createElement('div');
    menu.className = 'dropdown__menu';
    menu.setAttribute('role', 'listbox');
    menu.hidden = true;

    var selected = options.filter(function (o) { return o.value === selectedValue; })[0] || options[0];
    rotulo.textContent = selected ? selected.label : 'Selecione';

    var hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.setAttribute(hiddenAttr, '');
    hidden.value = selected ? selected.value : '';

    options.forEach(function (o) {
      var opt = document.createElement('button');
      opt.type = 'button';
      opt.setAttribute('role', 'option');
      opt.setAttribute('data-value', o.value);
      opt.className = 'dropdown__opt' + (selected && o.value === selected.value ? ' is-selected' : '');
      opt.appendChild(document.createTextNode(o.label));
      var check = document.createElement('i');
      check.className = 'fa-solid fa-check';
      opt.appendChild(check);

      opt.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.querySelectorAll('.dropdown__opt').forEach(function (o2) { o2.classList.toggle('is-selected', o2 === opt); });
        rotulo.textContent = o.label;
        hidden.value = o.value;
        hidden.dispatchEvent(new Event('change', { bubbles: true }));
        wrap.setAttribute('data-open', 'false');
        menu.hidden = true;
        btn.setAttribute('aria-expanded', 'false');
      });

      menu.appendChild(opt);
    });

    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      var aberto = wrap.getAttribute('data-open') === 'true';
      document.querySelectorAll('[data-dropdown]').forEach(function (d) {
        if (d === wrap) return;
        d.setAttribute('data-open', 'false');
        var m = d.querySelector('.dropdown__menu');
        var b = d.querySelector('[data-dropdown-btn]');
        if (m) m.hidden = true;
        if (b) b.setAttribute('aria-expanded', 'false');
      });
      wrap.setAttribute('data-open', String(!aberto));
      menu.hidden = aberto;
      btn.setAttribute('aria-expanded', String(!aberto));
    });

    wrap.appendChild(btn);
    wrap.appendChild(menu);
    wrap.appendChild(hidden);

    return wrap;
  }

  var state = { file: null, batchId: null, format: null, columns: [], rows: [], rowState: {}, pollTimer: null };

  /* ---------- Trilha de etapas ---------- */
  var ORDEM = ['upload', 'mapear', 'revisar', 'concluido'];

  function irPara(etapa) {
    root.querySelectorAll('[data-step]').forEach(function (secao) {
      secao.hidden = secao.getAttribute('data-step') !== etapa;
    });

    var idx = ORDEM.indexOf(etapa);
    root.querySelectorAll('[data-trail]').forEach(function (li) {
      var i = ORDEM.indexOf(li.getAttribute('data-trail'));
      var estado = i < idx ? 'done' : (i === idx ? 'current' : 'todo');
      li.setAttribute('data-state', estado);
      var passo = li.querySelector('.trail-step');
      passo.setAttribute('data-state', estado);
      var icone = passo.querySelector('.trail-step__bubble i');
      if (icone) icone.className = estado === 'done' ? 'fa-solid fa-check' : li.getAttribute('data-trail-icon');
    });

    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  root.querySelectorAll('[data-step-goto]').forEach(function (botao) {
    botao.addEventListener('click', function () {
      if (botao.hasAttribute('data-continue')) {
        doUpload();

        return;
      }
      irPara(botao.getAttribute('data-step-goto'));
    });
  });

  /* ---------- Etapa 1: arquivo ---------- */
  var dropzone = root.querySelector('[data-dropzone]');
  var chip = root.querySelector('[data-file-chip]');
  var chipName = root.querySelector('[data-file-name]');
  var chipMeta = root.querySelector('[data-file-meta]');
  var continueBtn = root.querySelector('[data-continue]');
  var fileInput = root.querySelector('[data-file-input]');

  function setFile(file) {
    state.file = file;
    if (!file) {
      chip.hidden = true;
      continueBtn.disabled = true;

      return;
    }
    chipName.textContent = file.name;
    chipMeta.textContent = (file.size / 1024).toFixed(0) + ' KB';
    chip.hidden = false;
    continueBtn.disabled = false;
  }

  if (dropzone) {
    dropzone.addEventListener('dragover', function (event) {
      event.preventDefault();
      dropzone.setAttribute('data-dragging', 'true');
    });
    dropzone.addEventListener('dragleave', function () {
      dropzone.setAttribute('data-dragging', 'false');
    });
    dropzone.addEventListener('drop', function (event) {
      event.preventDefault();
      dropzone.setAttribute('data-dragging', 'false');
      var arquivo = event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files[0];
      if (arquivo) setFile(arquivo);
    });
  }

  if (fileInput) {
    fileInput.addEventListener('change', function () {
      var arquivo = fileInput.files && fileInput.files[0];
      if (arquivo) setFile(arquivo);
    });
  }

  var removeBtn = root.querySelector('[data-file-remove]');
  if (removeBtn) {
    removeBtn.addEventListener('click', function (event) {
      event.preventDefault();
      if (fileInput) fileInput.value = '';
      setFile(null);
    });
  }

  function doUpload() {
    if (!state.file) return;

    var body = new FormData();
    var accountSelect = root.querySelector('[data-account-select]');
    body.append('account_id', accountSelect ? accountSelect.value : '');
    body.append('file', state.file);

    continueBtn.disabled = true;
    postJson(storeUrl, body).then(function (data) {
      state.batchId = data.batch_id;
      state.format = data.format;
      state.columns = data.columns || [];

      if (state.format === 'csv') {
        renderColumnFields(data.suggested_mapping || {});
        irPara('mapear');
      } else {
        irPara('revisar');
        startParse();
      }
    }).catch(function (error) {
      window.alert(error.message);
      continueBtn.disabled = false;
    });
  }

  /* ---------- Etapa 2: mapeamento (CSV) ---------- */
  var columnLabels = {
    date: 'Data do lançamento',
    description: 'Descrição',
    amount: 'Valor',
    external_id: 'Identificador (opcional)',
  };

  function renderColumnFields(suggested) {
    var wrap = root.querySelector('[data-column-fields]');
    wrap.innerHTML = '';

    Object.keys(columnLabels).forEach(function (field) {
      var label = document.createElement('label');
      label.className = 'map-field';

      var span = document.createElement('span');
      span.className = 'map-field__label';
      span.textContent = columnLabels[field];

      var options = [{ value: '', label: 'Não usar' }].concat(
        state.columns.map(function (coluna) { return { value: coluna, label: coluna }; })
      );
      var dropdown = createDropdown(options, suggested[field] || '', 'data-column-field');
      dropdown.querySelector('[data-column-field]').setAttribute('data-column-field', field);

      label.appendChild(span);
      label.appendChild(dropdown);
      wrap.appendChild(label);
    });
  }

  var parseBtn = root.querySelector('[data-action="parse"]');
  if (parseBtn) {
    parseBtn.addEventListener('click', function () {
      irPara('revisar');
      startParse();
    });
  }

  function startParse() {
    root.querySelector('[data-parsing-state]').hidden = false;
    root.querySelector('[data-review-content]').hidden = true;

    var columnMap = null;
    if (state.format === 'csv') {
      columnMap = {};
      root.querySelectorAll('[data-column-field]').forEach(function (select) {
        columnMap[select.getAttribute('data-column-field')] = select.value;
      });
    }

    var dateFormatEl = root.querySelector('[data-date-format]');
    var decimalEl = root.querySelector('[data-decimal-separator]');

    postJson(transactionsUrl + '/import/' + state.batchId + '/preview', JSON.stringify({
      column_map: columnMap,
      date_format: dateFormatEl ? dateFormatEl.value : null,
      decimal_separator: decimalEl ? decimalEl.value : null,
    })).then(function () {
      pollStatus();
    }).catch(function (error) {
      window.alert(error.message);
      irPara('mapear');
    });
  }

  function pollStatus() {
    window.clearTimeout(state.pollTimer);
    getJson(transactionsUrl + '/import/' + state.batchId).then(function (data) {
      if (data.status === 'parsing' || data.status === 'pending') {
        state.pollTimer = window.setTimeout(pollStatus, 1200);

        return;
      }

      if (data.status === 'failed') {
        window.alert(data.error || 'Falha ao ler o arquivo.');
        irPara('mapear');

        return;
      }

      state.rows = data.rows;
      state.rowState = {};
      data.rows.forEach(function (row) {
        state.rowState[row.id] = {
          included: row.included,
          categoryId: row.suggested_category_id,
          suggestedCategoryId: row.suggested_category_id,
        };
      });
      renderReview();
    });
  }

  /* ---------- Etapa 3: revisão ---------- */
  var entryTemplate = document.querySelector('[data-entry-template]');
  var currentFilter = 'todos';

  function entryState(row) {
    if (row.status === 'duplicate_exact' || row.status === 'duplicate_probable') return 'duplicado';
    if (row.status === 'needs_category' || row.status === 'invalid') return 'revisar';

    return row.suggested_category_id ? 'sugerido' : 'novo';
  }

  function renderReview() {
    root.querySelector('[data-parsing-state]').hidden = true;
    root.querySelector('[data-review-content]').hidden = false;

    var duplicates = state.rows.filter(function (row) {
      return row.status === 'duplicate_exact' || row.status === 'duplicate_probable';
    }).length;
    var needsCategory = state.rows.filter(function (row) { return !row.suggested_category_id; }).length;

    var summary = root.querySelector('[data-summary]');
    summary.innerHTML = '';
    [
      { icon: 'fa-list', label: 'Lançamentos lidos', value: state.rows.length },
      { icon: 'fa-clone', label: 'Já existem na conta', value: duplicates, tone: duplicates > 0 ? 'warn' : null },
      { icon: 'fa-circle-question', label: 'Precisam de categoria', value: needsCategory, tone: 'muted' },
    ].forEach(function (item) {
      var div = document.createElement('div');
      div.className = 'summary';
      var toneClass = item.tone ? ' summary__label--' + item.tone : '';
      var valueTone = item.tone === 'warn' ? ' summary__value--warn' : (item.tone === 'muted' ? ' summary__value--muted' : '');
      div.innerHTML =
        '<div class="summary__label' + toneClass + '"><i class="fa-solid ' + item.icon + '"></i>' + item.label + '</div>' +
        '<div class="summary__value' + valueTone + '">' + item.value + '</div>';
      summary.appendChild(div);
    });

    var entriesWrap = root.querySelector('[data-entries]');
    entriesWrap.innerHTML = '';

    state.rows.forEach(function (row, index) {
      var node = entryTemplate.content.cloneNode(true);
      var entryEl = node.querySelector('[data-entry]');
      var estado = entryState(row);
      entryEl.setAttribute('data-state', estado);
      if (estado === 'duplicado') entryEl.setAttribute('data-duplicate', 'true');

      var checkbox = node.querySelector('.entry__check');
      checkbox.checked = state.rowState[row.id].included;
      checkbox.addEventListener('change', function () {
        state.rowState[row.id].included = checkbox.checked;
        updateCounts();
      });

      node.querySelector('[data-entry-date]').textContent = row.posted_at;
      node.querySelector('[data-entry-name]').textContent = row.description;
      node.querySelector('[data-entry-source]').textContent =
        (row.type === 'income' ? 'Crédito' : 'Débito') + ' · linha ' + (index + 1);

      var flag = node.querySelector('[data-entry-flag]');
      if (estado === 'duplicado') {
        flag.hidden = false;
        flag.classList.add('entry__flag--dup');
        flag.innerHTML = '<i class="fa-solid fa-clone"></i>' + (row.status_label || 'Já registrado');
      } else if (row.status === 'needs_category') {
        flag.hidden = false;
        flag.innerHTML = '<i class="fa-solid fa-circle-question"></i>Sem categoria';
      } else {
        flag.hidden = true;
      }

      var dropdown = node.querySelector('[data-entry-category]');
      var dropdownLabel = dropdown.querySelector('[data-dropdown-label]');
      var initialCategoryId = row.suggested_category_id ? String(row.suggested_category_id) : '';
      dropdown.querySelectorAll('.dropdown__opt').forEach(function (opt) {
        var selected = opt.getAttribute('data-value') === initialCategoryId;
        opt.classList.toggle('is-selected', selected);
        if (selected && dropdownLabel) dropdownLabel.textContent = opt.textContent.trim();
      });
      // A troca visual (fechar menu, marcar .is-selected, atualizar o rótulo)
      // já é feita pelo listener delegado genérico em dashboard.js — aqui só
      // precisamos guardar a categoria escolhida no estado da linha.
      dropdown.addEventListener('click', function (e) {
        var opt = e.target.closest('.dropdown__opt');
        if (!opt) return;
        state.rowState[row.id].categoryId = opt.getAttribute('data-value') || null;
      });

      var valueEl = node.querySelector('[data-entry-value]');
      valueEl.textContent = (row.type === 'income' ? '+ ' : '− ') + formatMoney(row.amount);
      if (row.type === 'income') valueEl.classList.add('entry__value--in');

      entriesWrap.appendChild(node);
    });

    updateCounts();
    applyFilter(currentFilter);
  }

  function updateCounts() {
    var included = 0;
    Object.keys(state.rowState).forEach(function (id) {
      if (state.rowState[id].included) included++;
    });
    root.querySelector('[data-review-status]').textContent = included + ' de ' + state.rows.length + ' serão importados';
    root.querySelector('[data-confirm-label]').textContent = 'Importar ' + included + ' lançamentos';

    var counts = { todos: state.rows.length, duplicado: 0, revisar: 0 };
    state.rows.forEach(function (row) {
      var estado = entryState(row);
      if (estado === 'duplicado') counts.duplicado++;
      if (estado === 'revisar') counts.revisar++;
    });
    root.querySelectorAll('[data-count]').forEach(function (el) {
      el.textContent = counts[el.getAttribute('data-count')];
    });
  }

  function applyFilter(filtro) {
    currentFilter = filtro;
    root.querySelectorAll('[data-entry]').forEach(function (el) {
      el.hidden = filtro !== 'todos' && el.getAttribute('data-state') !== filtro;
    });
  }

  var filtersWrap = root.querySelector('[data-review-filters]');
  if (filtersWrap) {
    filtersWrap.querySelectorAll('[data-filter]').forEach(function (botao) {
      botao.addEventListener('click', function () {
        filtersWrap.querySelectorAll('[data-filter]').forEach(function (outro) {
          outro.setAttribute('aria-pressed', String(outro === botao));
        });
        applyFilter(botao.getAttribute('data-filter'));
      });
    });
  }

  var checkAll = root.querySelector('[data-check-all]');
  if (checkAll) {
    checkAll.addEventListener('change', function () {
      state.rows.forEach(function (row) {
        if (entryState(row) === 'duplicado') return;
        state.rowState[row.id].included = checkAll.checked;
      });
      renderReview();
    });
  }

  var commitBtn = root.querySelector('[data-action="commit"]');
  if (commitBtn) {
    commitBtn.addEventListener('click', function () {
      var rowIds = [];
      var categories = {};
      Object.keys(state.rowState).forEach(function (id) {
        if (state.rowState[id].included) {
          rowIds.push(Number(id));
          var entry = state.rowState[id];
          var changed = (entry.categoryId || null) !== (entry.suggestedCategoryId || null);
          if (changed) categories[id] = entry.categoryId || null;
        }
      });

      if (rowIds.length === 0) {
        window.alert('Selecione ao menos um lançamento.');

        return;
      }

      postJson(transactionsUrl + '/import/' + state.batchId + '/commit', JSON.stringify({
        row_ids: rowIds,
        categories: categories,
      })).then(function (result) {
        renderConcluido(result);
        irPara('concluido');
      }).catch(function (error) {
        window.alert(error.message);
      });
    });
  }

  /* ---------- Etapa 4: concluído ---------- */
  function renderConcluido(result) {
    root.querySelector('[data-final-title]').textContent =
      result.imported + ' lançamento' + (result.imported === 1 ? '' : 's') + ' importado' + (result.imported === 1 ? '' : 's') + '.';
    root.querySelector('[data-final-detail]').textContent =
      result.skipped + ' duplicado(s) ignorado(s). Seu painel está atualizado.';

    var wrap = root.querySelector('[data-final-summary]');
    wrap.innerHTML = '';
    [
      { icon: 'fa-check', label: 'Importados', value: result.imported, tone: 'accent' },
      { icon: 'fa-clone', label: 'Duplicados ignorados', value: result.skipped, tone: 'muted' },
      { icon: 'fa-wand-magic-sparkles', label: 'Categorizados automaticamente', value: result.auto_categorized },
    ].forEach(function (item) {
      var div = document.createElement('div');
      div.className = 'summary';
      var toneClass = item.tone ? ' summary__label--' + item.tone : '';
      var valueTone = item.tone ? ' summary__value--' + item.tone : '';
      div.innerHTML =
        '<div class="summary__label' + toneClass + '"><i class="fa-solid ' + item.icon + '"></i>' + item.label + '</div>' +
        '<div class="summary__value' + valueTone + '">' + item.value + '</div>';
      wrap.appendChild(div);
    });
  }

  var restartBtn = root.querySelector('[data-restart]');
  if (restartBtn) {
    restartBtn.addEventListener('click', function () {
      state.file = null;
      state.batchId = null;
      state.rows = [];
      state.rowState = {};
      if (fileInput) fileInput.value = '';
      setFile(null);
      irPara('upload');
    });
  }

  /* ---------- Tutorial por banco ---------- */
  var TUTORIAIS = {
    'Nubank': {
      passos: [
        'Abra o app do Nubank e toque em Conta, na tela inicial.',
        'Toque em Pedir extrato (ou Exportar extrato).',
        'Selecione o período desejado e confirme.',
        'O extrato chega no seu e-mail cadastrado em PDF, OFX e CSV. Baixe o OFX e traga para cá.',
      ],
      obs: 'O Nubank envia por e-mail, não baixa direto no app. Confira se o e-mail cadastrado está atualizado.',
    },
    'Itaú': {
      passos: [
        'Acesse o Internet Banking pelo computador.',
        'Vá em Conta corrente → Consultar extrato por período.',
        'Escolha o intervalo de datas e clique em Continuar.',
        'Selecione Salvar em outros formatos e escolha OFX / Money 2000.',
      ],
      obs: 'A exportação em OFX está disponível no Internet Banking, não no app.',
    },
    'Bradesco': {
      passos: [
        'Acesse o Internet Banking e vá em Menu → Saldos e Extratos → Extrato Mensal / Por Período.',
        'Selecione o período e a conta desejada e clique em Buscar.',
        'Com o extrato na tela, clique em Salvar como arquivo.',
        'Escolha OFX (Money 2000 em diante) e salve no computador.',
      ],
      obs: 'O Bradesco costuma disponibilizar apenas os últimos 60 dias. Exporte com frequência para não perder histórico.',
    },
    'Banco do Brasil': {
      passos: [
        'Acesse o Internet Banking e vá em Menu → Conta corrente → Extrato.',
        'Selecione o período desejado.',
        'Clique no ícone de download, na parte superior da tela.',
        'Escolha Money 2000+ (OFX) e salve o arquivo.',
      ],
      obs: 'O BB disponibiliza apenas os últimos 60 dias de extrato.',
    },
    'Caixa': {
      passos: [
        'Acesse o Internet Banking da Caixa e abra o menu ☰, no canto superior esquerdo.',
        'Na seção Extratos, escolha Conta por Período.',
        'Selecione o período desejado e clique em Gerar Arquivo para Gerenciadores Financeiros.',
        'Marque a opção OFX e clique em Continuar para baixar.',
      ],
      obs: 'A Caixa também limita o extrato aos últimos 60 dias.',
    },
    'Santander': {
      passos: [
        'Entre no Internet Banking e vá em Menu → Conta corrente → Extrato (Money).',
        'Selecione o período e clique em Exibir.',
        'No extrato, clique em Exportar.',
        'Escolha Money 2000 ou superior e salve o arquivo.',
      ],
      obs: '',
    },
    'Inter': {
      passos: [
        'Acesse o Internet Banking do Inter pelo navegador (o login pede o celular).',
        'No menu, entre em Conta Digital → Extrato.',
        'Selecione o período e clique em Aplicar.',
        'Clique em Exportar, no canto superior direito, e escolha OFX.',
      ],
      obs: 'A exportação em OFX fica no Internet Banking, não no app.',
    },
    'C6 Bank': {
      passos: [
        'Abra o app do C6 Bank e toque em Exibir extrato.',
        'Toque em Exportar extrato.',
        'Escolha um dos períodos sugeridos ou personalize as datas.',
        'Siga as instruções para receber o arquivo.',
      ],
      obs: 'O intervalo selecionado precisa estar dentro de 365 dias.',
    },
    'Outro banco': {
      passos: [
        'Abra o extrato da sua conta no app ou no site do banco.',
        'Procure por Exportar, Compartilhar ou Salvar em arquivo.',
        'Prefira OFX quando existir — a importação fica mais precisa. CSV também funciona.',
        'Baixe o arquivo e envie na etapa anterior.',
      ],
      obs: 'Se o seu banco não exporta OFX, o CSV resolve: você indica as colunas na etapa seguinte.',
    },
  };

  var bankSelect = root.querySelector('[data-bank-select]');
  var bankSteps = root.querySelector('[data-bank-steps]');
  var bankNote = root.querySelector('[data-bank-note]');

  function renderTutorial(banco) {
    var tutorial = TUTORIAIS[banco] || TUTORIAIS['Outro banco'];
    if (bankSteps) {
      bankSteps.innerHTML = '';
      tutorial.passos.forEach(function (texto, i) {
        var li = document.createElement('li');
        var num = document.createElement('span');
        num.className = 'tutorial__num';
        num.textContent = String(i + 1);
        var txt = document.createElement('span');
        txt.className = 'tutorial__text';
        txt.textContent = texto;
        li.appendChild(num);
        li.appendChild(txt);
        bankSteps.appendChild(li);
      });
    }
    if (bankNote) {
      bankNote.hidden = !tutorial.obs;
      var alvo = bankNote.querySelector('[data-bank-note-text]');
      if (alvo) alvo.textContent = tutorial.obs;
    }
  }

  if (bankSelect) {
    bankSelect.addEventListener('change', function () { renderTutorial(bankSelect.value); });
    renderTutorial(bankSelect.value);
  }

  setFile(null);
  irPara('upload');
})();
