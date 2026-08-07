/* financiaí — extensão do backend sobre o pacote de design.
   O dashboard.js do pacote cuida da parte visual dos dropdowns, date pickers,
   tiles de opção, swatches e switches (abrir/fechar, marcar .is-selected,
   is-on). Nenhum desses widgets tem <select>/<input> nativo por trás, então
   este arquivo novo só copia o valor escolhido para o campo oculto que o
   formulário de fato envia — sem duplicar nem substituir o comportamento
   visual do dashboard.js. */

(function () {
  /* ---------- Dropdowns genéricos: valor escolhido -> input oculto ---------- */
  document.querySelectorAll('[data-dropdown]').forEach(function (drop) {
    if (drop.hasAttribute('data-account-or-card')) return;
    var hidden = drop.querySelector('[data-dropdown-input]');
    if (!hidden) return;
    drop.querySelectorAll('.dropdown__opt').forEach(function (opt) {
      opt.addEventListener('click', function () {
        hidden.value = opt.getAttribute('data-value') || '';
        // Um <select> nativo dispara "change" sozinho; um <input type="hidden">
        // não dispara nada só por ter o .value trocado via JS. Sem isso, quem
        // escuta "change" nesse campo (ex.: o tutorial por banco da importação)
        // nunca é avisado da troca.
        hidden.dispatchEvent(new Event('change', { bubbles: true }));
      });
    });
  });

  /* ---------- Dropdown misto "conta ou cartão" (modal de transação) ---------- */
  document.querySelectorAll('[data-account-or-card]').forEach(function (drop) {
    var wrap = drop.parentElement;
    if (!wrap) return;
    var channel = wrap.querySelector('[data-payment-channel]');
    var accountField = wrap.querySelector('[data-account-id-field]');
    var cardField = wrap.querySelector('[data-credit-card-id-field]');
    if (!channel || !accountField || !cardField) return;

    drop.querySelectorAll('.dropdown__opt').forEach(function (opt) {
      opt.addEventListener('click', function () {
        if (opt.hasAttribute('data-account-id')) {
          channel.value = 'account';
          accountField.value = opt.getAttribute('data-account-id');
          cardField.value = '';
        } else if (opt.hasAttribute('data-credit-card-id')) {
          channel.value = 'credit_card';
          cardField.value = opt.getAttribute('data-credit-card-id');
          accountField.value = '';
        }
      });
    });
  });

  /* ---------- Chips com valor (tipo de transação, bandeira, etc.) ---------- */
  document.querySelectorAll('[data-chip-group]').forEach(function (group) {
    var hidden = group.parentElement ? group.parentElement.querySelector('[data-chip-input]') : null;
    if (!hidden) return;
    group.querySelectorAll('.chip').forEach(function (chip) {
      chip.addEventListener('click', function () {
        hidden.value = chip.getAttribute('data-value') || '';
      });
    });
  });

  /* ---------- Nova conta manual: tipo, cor e switch "somar no saldo total" ---------- */
  var tipoGrupo = document.querySelector('[data-account-type]');
  var tipoInput = document.getElementById('novaConta-type');
  var iconeInput = document.getElementById('novaConta-icon');
  if (tipoGrupo && tipoInput && iconeInput) {
    tipoGrupo.querySelectorAll('.option-tile').forEach(function (tile) {
      tile.addEventListener('click', function () {
        tipoInput.value = tile.getAttribute('data-type') || '';
        iconeInput.value = tile.getAttribute('data-icon-key') || '';
      });
    });
  }

  var coresGrupo = document.querySelector('[data-account-colors]');
  var corInput = document.getElementById('novaConta-color');
  if (coresGrupo && corInput) {
    coresGrupo.querySelectorAll('.swatch').forEach(function (sw) {
      sw.addEventListener('click', function () {
        corInput.value = sw.getAttribute('data-value') || '';
      });
    });
  }

  var switchTotal = document.querySelector('[data-account-total]');
  var ativoInput = document.getElementById('novaConta-active');
  if (switchTotal && ativoInput) {
    switchTotal.addEventListener('click', function () {
      ativoInput.value = switchTotal.classList.contains('is-on') ? '1' : '0';
    });
  }

  /* ---------- Configurações > Perfil: remover foto salva no servidor ---------- */
  var removeAvatarBtn = document.getElementById('avatarRemove');
  var removeAvatarFlag = document.getElementById('removeAvatarFlag');
  var avatarInput = document.getElementById('avatarInput');
  if (removeAvatarBtn && removeAvatarFlag) {
    removeAvatarBtn.addEventListener('click', function () {
      removeAvatarFlag.value = '1';
    });
  }
  if (avatarInput && removeAvatarFlag) {
    avatarInput.addEventListener('change', function () {
      if (avatarInput.files && avatarInput.files[0]) removeAvatarFlag.value = '0';
    });
  }
})();
