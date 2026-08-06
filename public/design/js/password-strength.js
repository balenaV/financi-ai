/* financiaí — extensão do backend/produto sobre o pacote de design.
   Indicador de força de senha no formulário de criar conta. Arquivo novo:
   não edita o auth.js copiado do pacote de referência. */

(function () {
  var input = document.querySelector('[data-password-strength-input]');
  var box = document.querySelector('[data-password-strength]');
  if (!input || !box) return;

  var fill = box.querySelector('[data-password-strength-fill]');
  var label = box.querySelector('[data-password-strength-label]');

  var LEVELS = [
    { min: 0, name: 'weak', text: 'Senha fraca' },
    { min: 3, name: 'medium', text: 'Senha média' },
    { min: 4, name: 'strong', text: 'Senha forte' },
  ];

  function score(value) {
    var s = 0;
    if (value.length >= 8) s++;
    if (value.length >= 12) s++;
    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) s++;
    if (/[0-9]/.test(value)) s++;
    if (/[^A-Za-z0-9]/.test(value)) s++;
    return s;
  }

  function levelFor(s) {
    var level = LEVELS[0];
    for (var i = 0; i < LEVELS.length; i++) {
      if (s >= LEVELS[i].min) level = LEVELS[i];
    }
    return level;
  }

  input.addEventListener('input', function () {
    var value = input.value;
    if (!value) {
      box.hidden = true;
      return;
    }
    box.hidden = false;
    var s = score(value);
    var level = levelFor(s);
    box.setAttribute('data-level', level.name);
    if (fill) fill.style.width = Math.max(12, (s / 5) * 100) + '%';
    if (label) label.textContent = level.text;
  });
})();
