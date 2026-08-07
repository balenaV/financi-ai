@php
    $mode = ($mode ?? 'login') === 'registro' ? 'registro' : 'login';
    $copy = [
        'login' => [
            'titulo' => 'Bem-vindo de volta.',
            'subtitulo' => 'Entre para acompanhar suas contas, metas e compromissos dos próximos meses.',
        ],
        'registro' => [
            'titulo' => 'Comece a organizar hoje.',
            'subtitulo' => 'Crie sua conta gratuita e registre sua realidade financeira em poucos minutos.',
        ],
    ];
@endphp
<x-auth-layout :title="$mode === 'registro' ? 'Criar conta' : 'Entrar'">

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

        <h1 class="auth__title" data-auth-title>{{ $copy[$mode]['titulo'] }}</h1>
        <p class="auth__subtitle" data-auth-subtitle>{{ $copy[$mode]['subtitulo'] }}</p>

        @if(config('features.registration'))
            <div class="tabs auth__tabs" role="tablist" data-tabs data-active="{{ $mode }}">
                <span class="tabs__indicator" aria-hidden="true"></span>
                <button class="tabs__btn" type="button" role="tab" data-tab="login" data-url="{{ route('login') }}" aria-selected="{{ $mode === 'login' ? 'true' : 'false' }}">Entrar</button>
                <button class="tabs__btn" type="button" role="tab" data-tab="registro" data-url="{{ route('register') }}" aria-selected="{{ $mode === 'registro' ? 'true' : 'false' }}">Criar conta</button>
            </div>
        @endif

        @if (session('status'))
            <p class="form-status">{{ session('status') }}</p>
        @endif

        <div class="auth__panes">

          <form class="auth-form auth-form--login" data-pane="login" data-state="{{ $mode === 'login' ? 'active' : 'hidden' }}" method="post" action="{{ route('login') }}">
            @csrf

            @if ($errors->has('social'))
                <div class="form-alert" role="alert">{{ $errors->first('social') }}</div>
            @endif

            <div class="auth__social">
                <a class="btn-social" href="{{ route('social.redirect', ['provider' => 'google']) }}">
                    <svg class="btn-social__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5a5.6 5.6 0 0 1-2.4 3.6v3h3.9c2.2-2.1 3.5-5.2 3.5-8.8zM12 24c3.2 0 5.9-1.1 7.9-2.9l-3.9-3c-1.1.7-2.4 1.1-4 1.1-3.1 0-5.7-2.1-6.6-4.9H1.4v3.1A12 12 0 0 0 12 24zM5.4 14.3a7.2 7.2 0 0 1 0-4.6V6.6H1.4a12 12 0 0 0 0 10.8l4-3.1zM12 4.8c1.8 0 3.3.6 4.6 1.8l3.4-3.4C17.9 1.2 15.2 0 12 0 7.3 0 3.3 2.7 1.4 6.6l4 3.1C6.3 6.9 8.9 4.8 12 4.8z"/></svg>
                    Google
                </a>
                <a class="btn-social" href="{{ route('social.redirect', ['provider' => 'github']) }}">
                    <svg class="btn-social__icon" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38l-.01-1.34c-2.23.48-2.7-1.07-2.7-1.07-.36-.93-.89-1.18-.89-1.18-.73-.5.05-.49.05-.49.81.06 1.23.83 1.23.83.72 1.23 1.88.87 2.34.67.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82a7.6 7.6 0 0 1 4 0c1.53-1.03 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.28.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48l-.01 2.2c0 .21.15.46.55.38A8 8 0 0 0 16 8c0-4.42-3.58-8-8-8z"/></svg>
                    GitHub
                </a>
            </div>

            <div class="divider">
                <span class="divider__line"></span>
                <span class="divider__label">ou continue com e-mail</span>
                <span class="divider__line"></span>
            </div>

            <label class="field">
              <span class="field__label">E-mail</span>
              <input class="input" type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="voce@email.com">
              @error('email', 'login') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label class="field">
              <span class="field__label-row">
                Senha
                <a class="link-sm" href="{{ route('password.request') }}">Esqueci minha senha</a>
              </span>
              <span class="input-group">
                <input class="input-group__input" type="password" name="password" autocomplete="current-password" placeholder="••••••••">
                <button class="input-group__action" type="button" data-password-toggle aria-label="Mostrar ou ocultar senha">Mostrar</button>
              </span>
              @error('password', 'login') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label class="checkbox-row">
              <input class="checkbox" type="checkbox" name="remember">
              Manter conectado neste dispositivo
            </label>

            <button class="btn-primary" type="submit">Entrar na minha conta</button>
          </form>

          @if(config('features.registration'))
              <form class="auth-form auth-form--registro" data-pane="registro" data-state="{{ $mode === 'registro' ? 'active' : 'hidden' }}" method="post" action="{{ route('register') }}">
                @csrf

                <label class="field">
                  <span class="field__label">Nome completo</span>
                  <input class="input" type="text" name="name" value="{{ old('name') }}" autocomplete="name" placeholder="Como você quer ser chamado">
                  @error('name', 'registro') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                  <span class="field__label">E-mail</span>
                  <input class="input" type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="voce@email.com">
                  @error('email', 'registro') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                  <span class="field__label">Senha</span>
                  <span class="input-group">
                    <input class="input-group__input" type="password" name="password" autocomplete="new-password" placeholder="Mínimo de 8 caracteres" data-password-strength-input>
                    <button class="input-group__action" type="button" data-password-toggle aria-label="Mostrar ou ocultar senha">Mostrar</button>
                  </span>
                  <div class="password-strength" data-password-strength hidden>
                    <div class="password-strength__track"><span class="password-strength__fill" data-password-strength-fill></span></div>
                    <span class="password-strength__label" data-password-strength-label></span>
                  </div>
                  @error('password', 'registro') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <label class="field">
                  <span class="field__label">Confirmar senha</span>
                  <input class="input" type="password" name="password_confirmation" autocomplete="new-password" placeholder="Repita a senha">
                </label>

                <label class="checkbox-row checkbox-row--top">
                  <input class="checkbox" type="checkbox" name="terms" required>
                  <span>Li e aceito os <a href="#termos">termos de uso</a> e a <a href="#privacidade">política de privacidade</a>.</span>
                </label>
                @error('terms', 'registro') <span class="field-error">{{ $message }}</span> @enderror

                <button class="btn-primary" type="submit">Criar minha conta</button>
              </form>
          @endif

        </div>

        <p class="auth__disclaimer">O financiaí não movimenta o seu dinheiro e não conecta contas bancárias. Você registra, ele organiza.</p>

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
