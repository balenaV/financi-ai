<x-auth-layout title="Verificar e-mail">

<div class="auth">

  <section class="auth__form-side">

    <div class="auth__top">
      <a class="brand" href="{{ url('/') }}">
        <img class="brand__mark" src="{{ asset('design/assets/capi/capi-rosto.png') }}" alt="">
        <span class="brand__name">financi<span class="brand__name-accent">aí</span></span>
      </a>
      <div class="auth__top-actions">
        <button class="btn-icon" type="button" data-theme-toggle aria-label="Alternar tema">☾</button>
        <a class="btn-outline-hard" href="{{ url('/') }}">Voltar ao site</a>
      </div>
    </div>

    <div class="auth__center">
      <div class="auth__card">

        <h1 class="auth__title">Confira sua caixa de entrada.</h1>
        <p class="auth__subtitle">Enviamos um link para confirmar seu endereço. Se ele não chegou, você pode solicitar outro.</p>

        @if (session('status') === 'verification-link-sent')
            <p class="form-status">Um novo link de verificação foi enviado.</p>
        @endif

        <div class="auth-actions">
          <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="btn-primary" type="submit">Reenviar e-mail</button>
          </form>

          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn-outline-hard auth-actions__ghost" type="submit">Sair da conta</button>
          </form>
        </div>

      </div>
    </div>

    <div class="auth__footer">
      <span>© {{ now()->year }} financiaí</span>
      <a href="#privacidade">Privacidade</a>
      <a href="#termos">Termos</a>
      <a href="#ajuda">Ajuda</a>
    </div>

  </section>

  <aside class="auth__aside">
    <div class="promo">

      <span class="promo__ring" aria-hidden="true"></span>
      <span class="promo__dot promo__dot--a" aria-hidden="true"></span>
      <span class="promo__dot promo__dot--b" aria-hidden="true"></span>

      <div class="promo__head">
        <span class="badge"><span class="badge__dot"></span>Beta aberto</span>
        <h2 class="promo__title">Sua vida financeira inteira em um só painel.</h2>
        <p class="promo__text">Contas, cartões, metas e compromissos futuros organizados para você decidir com clareza — hoje e nos próximos doze meses.</p>
      </div>

      <div class="promo__proofs">
        <div class="proof">
          <span class="proof__check" aria-hidden="true">✓</span>
          <span class="proof__text">Contas, cartões e faturas no mesmo painel</span>
        </div>
        <div class="proof">
          <span class="proof__check" aria-hidden="true">✓</span>
          <span class="proof__text">Receitas e despesas futuras já projetadas</span>
        </div>
        <div class="proof">
          <span class="proof__check" aria-hidden="true">✓</span>
          <span class="proof__text">Dados isolados por usuário e histórico de segurança</span>
        </div>
        <div class="proof">
          <span class="proof__check" aria-hidden="true">✓</span>
          <span class="proof__text">Exportação dos seus dados quando quiser</span>
        </div>
      </div>

      <div class="promo__bottom">
        <img class="promo__capi" src="{{ asset('design/assets/capi/capi-comemorando.png') }}" alt="Capí comemorando">
        <div class="promo__stats">
          <div class="stat">
            <div class="stat__value">12 meses</div>
            <div class="stat__label">de visão futura</div>
          </div>
          <div class="stat">
            <div class="stat__value">100%</div>
            <div class="stat__label">dados isolados</div>
          </div>
        </div>
      </div>

    </div>
  </aside>

</div>

</x-auth-layout>
