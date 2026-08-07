@php
    $ctaRoute = auth()->check()
        ? route('dashboard')
        : (config('features.registration') ? route('register') : route('login'));
    $ctaLabelPrimary = auth()->check() ? 'Abrir meu painel' : 'Começar agora';
    $ctaLabelCreate = auth()->check() ? 'Abrir meu painel' : 'Criar conta gratuitamente';
@endphp
<x-landing-layout>

<header class="site-header" data-header>
  <a class="brand" href="#topo">
    <img class="brand__mark" src="{{ asset('design/assets/capi/capi-rosto.png') }}" alt="">
    <span class="brand__name">financi<span class="brand__name-accent">aí</span></span>
  </a>

  <nav class="site-nav">
    <a class="site-nav__link" href="#recursos">Recursos</a>
    <a class="site-nav__link" href="#como-funciona">Como funciona</a>
    <a class="site-nav__link" href="#agentes">Agentes</a>
    <a class="site-nav__link" href="#seguranca">Segurança</a>
    <a class="site-nav__link" href="#planos">Planos</a>
  </nav>

  <div class="site-header__actions">
    <button class="btn-icon" type="button" data-theme-toggle aria-label="Alternar tema">☾</button>
    @auth
        <a class="btn-cta-sm" href="{{ route('dashboard') }}">Abrir painel</a>
    @else
        <a class="header-login" href="{{ route('login') }}">Entrar</a>
        <a class="btn-cta-sm" href="{{ $ctaRoute }}">Começar grátis</a>
    @endauth
    <button class="btn-burger" type="button" data-burger aria-label="Menu" aria-expanded="false">☰</button>
  </div>
</header>

<div class="mobile-menu" data-mobile-menu hidden>
  <a href="#recursos">Recursos</a>
  <a href="#como-funciona">Como funciona</a>
  <a href="#agentes">Agentes</a>
  <a href="#seguranca">Segurança</a>
  <a href="#planos">Planos</a>
  @auth
      <a class="mobile-menu__divider" href="{{ route('dashboard') }}">Abrir painel</a>
  @else
      <a class="mobile-menu__divider" href="{{ route('login') }}">Entrar</a>
  @endauth
</div>

<main class="landing__main" id="topo">

  <section class="hero">
    <div class="hero__copy">
      <span class="pill-badge"><span class="pill-badge__dot"></span>Clareza para decidir melhor todos os dias</span>
      <h1 class="hero__title">Organize suas finanças hoje para viver melhor <em>amanhã</em>.</h1>
      <p class="hero__lead">Entenda o presente, planeje os próximos meses e acompanhe sua vida financeira com clareza, segurança e um Capí sempre por perto.</p>
      <div class="hero__actions">
        <a class="btn-cta-lg" href="{{ $ctaRoute }}">{{ $ctaLabelPrimary }}</a>
        <a class="btn-ghost-lg" href="#recursos">Conhecer recursos</a>
      </div>
      <p class="hero__note">Sem conexão bancária automática. Você registra, o financiaí organiza.</p>
    </div>

    <div class="hero__visual">
      <div class="hero__glow" aria-hidden="true"></div>

      <div class="mock" aria-hidden="true">
        <div class="mock__top">
          <div>
            <div class="mock__label">Saldo consolidado</div>
            <div class="mock__value">R$ 12.480,<small>35</small></div>
          </div>
          <span class="mock__tag">demonstrativo</span>
        </div>

        <div class="mock__chart">
          <div class="mock__bar mock__bar--1"></div>
          <div class="mock__bar mock__bar--2"></div>
          <div class="mock__bar mock__bar--3"></div>
          <div class="mock__bar mock__bar--4"></div>
          <div class="mock__bar mock__bar--5"></div>
          <div class="mock__bar mock__bar--6"></div>
        </div>
        <div class="mock__axis"><span>abr</span><span>mai</span><span>jun</span><span>jul</span><span>ago</span><span>set</span></div>

        <div class="mock__rows">
          <div class="mock__row">
            <span class="mock__row-label">Fatura do cartão</span>
            <span class="mock__row-value mock__row-value--caramel">R$ 1.842,10</span>
          </div>
          <div class="mock__row">
            <span class="mock__row-label">Meta: reserva de emergência</span>
            <span class="mock__row-value mock__row-value--forest">68%</span>
          </div>
        </div>
      </div>

      <img class="hero__capi" src="{{ asset('design/assets/capi/capi-celular.png') }}" alt="Capí apoiado no aplicativo financiaí">
    </div>
  </section>

  <section class="section" id="recursos">
    <h2 class="section__title" data-reveal>Tudo o que você precisa para enxergar sua vida financeira inteira.</h2>
    <div class="feature-grid">
      <article class="feature" data-reveal>
        <div class="feature__num">01</div>
        <h3 class="feature__title">Tudo em um só lugar</h3>
        <p class="feature__text">Contas, cartões, metas, dívidas e investimentos convivendo no mesmo painel, sem planilhas paralelas.</p>
      </article>
      <article class="feature" data-reveal>
        <div class="feature__num">02</div>
        <h3 class="feature__title">Planejamento que olha adiante</h3>
        <p class="feature__text">Receitas, despesas e compromissos futuros registrados hoje viram previsão dos próximos meses.</p>
      </article>
      <article class="feature" data-reveal>
        <div class="feature__num">03</div>
        <h3 class="feature__title">Segurança de verdade</h3>
        <p class="feature__text">Seus dados ficam isolados por usuário, com autenticação, controle de sessão e histórico de acessos.</p>
      </article>
      <article class="feature" data-reveal>
        <div class="feature__num">04</div>
        <h3 class="feature__title">Clareza para decidir</h3>
        <p class="feature__text">Relatórios e projeções escritos em português claro — números que explicam, não que assustam.</p>
      </article>
    </div>
  </section>

  <section class="section" id="como-funciona">
    <div class="steps-card" data-reveal>
      <div>
        <span class="eyebrow">Como funciona</span>
        <h2 class="steps-card__title">Três passos, e o resto é constância.</h2>
        <ol class="steps">
          <li class="step">
            <span class="step__num">1</span>
            <div>
              <div class="step__title">Registre sua realidade financeira</div>
              <div class="step__text">Contas, cartões, dívidas e o que entra todo mês.</div>
            </div>
          </li>
          <li class="step">
            <span class="step__num">2</span>
            <div>
              <div class="step__title">Acompanhe o presente e os próximos meses</div>
              <div class="step__text">Planejamento mensal, faturas e compromissos futuros no mesmo lugar.</div>
            </div>
          </li>
          <li class="step">
            <span class="step__num">3</span>
            <div>
              <div class="step__title">Ajuste decisões e avance nas suas metas</div>
              <div class="step__text">Pequenos passos constroem grandes mudanças.</div>
            </div>
          </li>
        </ol>
      </div>
      <div class="steps-card__art">
        <img src="{{ asset('design/assets/capi/capi-apontando.png') }}" alt="Capí apontando os três passos">
      </div>
    </div>
  </section>

  <section class="section" id="agentes">
    <div class="agents" data-reveal>
      <div class="agents__intro">
        <h2>Agentes em breve</h2>
        <p>Personagens que vão te ajudar em cada etapa da sua jornada.</p>
      </div>

      <article class="agent">
        <span class="agent__initial">D</span>
        <div class="agent__body">
          <div class="agent__head">
            <h3 class="agent__name">Dumont</h3>
            <span class="agent__soon">em breve</span>
          </div>
          <p class="agent__text">Viagens e planos que cabem no bolso.</p>
        </div>
      </article>

      <article class="agent">
        <span class="agent__initial">D</span>
        <div class="agent__body">
          <div class="agent__head">
            <h3 class="agent__name">Dinho</h3>
            <span class="agent__soon">em breve</span>
          </div>
          <p class="agent__text">Rolês e gastos do dia a dia equilibrados.</p>
        </div>
      </article>

      <article class="agent">
        <span class="agent__initial">C</span>
        <div class="agent__body">
          <div class="agent__head">
            <h3 class="agent__name">Chamaí</h3>
            <span class="agent__soon">em breve</span>
          </div>
          <p class="agent__text">Foco, disciplina e constância na rotina.</p>
        </div>
      </article>
    </div>
    <p class="agents__note">Personagens conceituais. Nenhuma inteligência artificial está ativa no produto hoje.</p>
  </section>

  <section class="section">
    <div class="stats-row" data-reveal>
      <div class="stat-card">
        <div class="stat-card__value">12 meses</div>
        <div class="stat-card__label">de visão futura no planejamento</div>
      </div>
      <div class="stat-card">
        <div class="stat-card__value">1 painel</div>
        <div class="stat-card__label">para toda a sua vida financeira</div>
      </div>
      <div class="stat-card">
        <div class="stat-card__value">100%</div>
        <div class="stat-card__label">dos dados isolados por usuário</div>
      </div>
      <div class="stat-card">
        <div class="stat-card__value">Centavo</div>
        <div class="stat-card__label">a centavo: precisão nos cálculos</div>
      </div>
    </div>
  </section>

  <section class="section" id="seguranca">
    <div class="security" data-reveal>
      <div class="security__art">
        <img src="{{ asset('design/assets/capi/capi-sentado.png') }}" alt="Capí tranquilo ao lado de uma planta">
      </div>
      <div>
        <span class="eyebrow">Segurança</span>
        <h2 class="security__title">O financiaí não movimenta o seu dinheiro.</h2>
        <p class="security__text">Ele organiza a informação. Nada de conexão automática com bancos: você registra, e o sistema cuida da clareza.</p>
        <ul class="check-list">
          <li><span class="check-list__mark">✓</span><span>Dados isolados por usuário, sem mistura entre contas</span></li>
          <li><span class="check-list__mark">✓</span><span>Autenticação e verificação de e-mail</span></li>
          <li><span class="check-list__mark">✓</span><span>Histórico de segurança e controle de sessão</span></li>
          <li><span class="check-list__mark">✓</span><span>Exportação dos seus dados quando quiser</span></li>
          <li><span class="check-list__mark">✓</span><span>Nenhuma movimentação bancária automática</span></li>
          <li><span class="check-list__mark">✓</span><span>Nenhuma conexão com bancos ou Open Finance</span></li>
        </ul>
      </div>
    </div>
  </section>

  <section class="section" id="planos">
    <div class="plans__head" data-reveal>
      <h2>Planos em definição, transparência desde já.</h2>
      <p>Durante o beta, os valores ainda estão sendo definidos. O escopo de cada plano é este:</p>

      <div class="cycle-toggle" data-cycle-toggle>
        <span class="cycle-toggle__pill" aria-hidden="true"></span>
        <button class="cycle-toggle__btn" type="button" data-cycle="mensal" aria-pressed="true">Mensal</button>
        <button class="cycle-toggle__btn" type="button" data-cycle="anual" aria-pressed="false">Anual<span class="cycle-toggle__save">−30%</span></button>
      </div>
    </div>

    <div class="plans-grid">
      <article class="plan" data-reveal>
        <div class="plan__kicker">Comece simples</div>
        <h3 class="plan__name">Organização essencial</h3>
        <p class="plan__text">Tudo para registrar e entender sua movimentação diária.</p>
        <div class="plan__price-box">
          <div class="plan__price"><span data-price="essencial">R$ 19,90</span><span class="plan__price-unit">/mês</span></div>
          <div class="plan__cycle-note" data-cycle-note>cobrado mensalmente · valores de referência do beta</div>
        </div>
        <ul class="plan__features">
          <li>Contas e transações</li>
          <li>Cartões, faturas e parcelamentos</li>
          <li>Categorias personalizadas</li>
          <li>Receitas e despesas futuras</li>
          <li>Orçamentos e metas</li>
        </ul>
        <a class="plan__action" href="{{ $ctaRoute }}">Organizar minhas finanças</a>
      </article>

      <article class="plan plan--featured" data-reveal>
        <span class="plan__ribbon">Visão completa</span>
        <div class="plan__kicker">Veja além do mês</div>
        <h3 class="plan__name">Planejamento integrado</h3>
        <p class="plan__text">Conecte compromissos, patrimônio e objetivos em uma única visão.</p>
        <div class="plan__price-box">
          <div class="plan__price"><span data-price="completo">R$ 35,90</span><span class="plan__price-unit">/mês</span></div>
          <div class="plan__cycle-note" data-cycle-note>cobrado mensalmente · valores de referência do beta</div>
        </div>
        <ul class="plan__features">
          <li>Tudo do Essencial</li>
          <li>Empréstimos e dívidas</li>
          <li>Investimentos e patrimônio</li>
          <li>Relatórios e projeções</li>
          <li>Agentes temáticos quando existirem</li>
        </ul>
        <a class="plan__action plan__action--primary" href="{{ $ctaRoute }}">Ter visão completa</a>
      </article>
    </div>
  </section>

  <section class="section section--narrow">
    <h2 class="faq-title" data-reveal>Perguntas frequentes</h2>
    <div class="faq-list">
      <details class="faq-item">
        <summary>O financiaí movimenta meu dinheiro?<span class="faq-item__sign">+</span></summary>
        <p>Não. O financiaí é uma ferramenta de organização e planejamento. Ele não faz pagamentos, transferências nem qualquer movimentação financeira.</p>
      </details>
      <details class="faq-item">
        <summary>Preciso conectar minha conta bancária?<span class="faq-item__sign">+</span></summary>
        <p>Não existe conexão automática com bancos. Você registra contas, lançamentos e faturas, e o sistema organiza tudo em um painel único.</p>
      </details>
      <details class="faq-item">
        <summary>Como meus dados são protegidos?<span class="faq-item__sign">+</span></summary>
        <p>Cada usuário tem seus dados isolados, com autenticação, verificação de e-mail, controle de sessão e histórico de segurança.</p>
      </details>
      <details class="faq-item">
        <summary>Posso registrar receitas e despesas futuras?<span class="faq-item__sign">+</span></summary>
        <p>Sim. Lançamentos futuros, parcelamentos e compromissos entram na projeção dos próximos meses.</p>
      </details>
      <details class="faq-item">
        <summary>Os Agentes já funcionam com IA?<span class="faq-item__sign">+</span></summary>
        <p>Ainda não. Dumont, Dinho e Chamaí são personagens conceituais. Nenhum recurso de inteligência artificial está ativo no produto.</p>
      </details>
      <details class="faq-item">
        <summary>Posso exportar meus dados?<span class="faq-item__sign">+</span></summary>
        <p>Sim. A exportação dos seus registros está disponível para que a informação continue sendo sua.</p>
      </details>
      <details class="faq-item">
        <summary>Como os planos vão funcionar?<span class="faq-item__sign">+</span></summary>
        <p>Os escopos já estão definidos, mas os valores serão anunciados antes do lançamento. Durante o beta nada é cobrado.</p>
      </details>
    </div>
  </section>

  <section class="section cta-section" id="cta">
    <div class="cta" data-reveal>
      <span class="cta__ring" aria-hidden="true"></span>
      <span class="cta__dot cta__dot--a" aria-hidden="true"></span>
      <span class="cta__dot cta__dot--b" aria-hidden="true"></span>

      <div class="cta__copy">
        <h2 class="cta__title">Pronto para enxergar suas finanças com <em>clareza</em>?</h2>
        <p class="cta__text">Crie sua conta, registre sua realidade financeira e transforme seus próximos passos em um plano objetivo.</p>
      </div>

      <div class="cta__side">
        <a class="cta__button" href="{{ $ctaRoute }}">{{ $ctaLabelCreate }} <span>→</span></a>
        <img class="cta__capi" src="{{ asset('design/assets/capi/capi-comemorando.png') }}" alt="Capí comemorando">
      </div>
    </div>
  </section>

</main>

<footer class="site-footer">
  <div class="site-footer__grid">
    <div class="site-footer__about">
      <div class="brand">
        <img class="brand__mark" src="{{ asset('design/assets/capi/capi-rosto.png') }}" alt="">
        <span class="brand__name">financi<span class="brand__name-accent">aí</span></span>
      </div>
      <p>Gerenciador financeiro pessoal brasileiro. Organize hoje, respire melhor amanhã.</p>
    </div>

    <div>
      <div class="site-footer__heading">Produto</div>
      <div class="site-footer__links">
        <a href="#recursos">Recursos</a>
        <a href="#como-funciona">Como funciona</a>
        <a href="#agentes">Agentes</a>
        <a href="#planos">Planos</a>
      </div>
    </div>

    <div>
      <div class="site-footer__heading">Empresa</div>
      <div class="site-footer__links">
        <a href="#sobre">Sobre</a>
        <a href="#blog">Blog</a>
        <a href="#seguranca">Segurança</a>
        <a href="#privacidade">Privacidade</a>
        <a href="#termos">Termos</a>
      </div>
    </div>

    <div>
      <div class="site-footer__heading">Acesso</div>
      <div class="site-footer__links">
        @auth
            <a href="{{ route('dashboard') }}">Abrir painel</a>
        @else
            <a href="{{ route('login') }}">Entrar</a>
            @if(config('features.registration'))
                <a href="{{ route('register') }}">Criar conta</a>
            @endif
            <a href="{{ route('password.request') }}">Recuperar senha</a>
        @endauth
      </div>
    </div>
  </div>

  <div class="site-footer__bottom">
    <span>© {{ now()->year }} financiaí</span>
    <span>Feito para decisões financeiras mais claras.</span>
  </div>
</footer>

</x-landing-layout>
