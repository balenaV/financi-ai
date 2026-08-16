@props(['prefix'])
<div class="pw-meter" id="{{ $prefix }}Meter" hidden>
  <div class="pw-meter__bars" aria-hidden="true">
    <span class="pw-meter__bar"></span><span class="pw-meter__bar"></span><span class="pw-meter__bar"></span><span class="pw-meter__bar"></span>
  </div>
  <span class="pw-meter__label" role="status"></span>
</div>
<ul class="pw-rules" id="{{ $prefix }}Rules" hidden>
  <li class="pw-rule" data-rule="tamanho"><span class="pw-rule__mark" aria-hidden="true">·</span>Ao menos 8 caracteres</li>
  <li class="pw-rule" data-rule="maiuscula"><span class="pw-rule__mark" aria-hidden="true">·</span>Uma letra maiúscula</li>
  <li class="pw-rule" data-rule="minuscula"><span class="pw-rule__mark" aria-hidden="true">·</span>Uma letra minúscula</li>
  <li class="pw-rule" data-rule="numero"><span class="pw-rule__mark" aria-hidden="true">·</span>Um número</li>
  <li class="pw-rule" data-rule="simbolo"><span class="pw-rule__mark" aria-hidden="true">·</span>Um símbolo ! @ # $ % &amp; * ? _ - .</li>
</ul>
<p class="pw-error" id="{{ $prefix }}Error" hidden></p>
