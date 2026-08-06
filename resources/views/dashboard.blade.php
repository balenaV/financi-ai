@php
    use App\Support\Money;
    use Illuminate\Support\Str;

    $user = auth()->user();
    $userSettings = $user->settings()->firstOrCreate();
    $hide = (bool) $userSettings->hide_values;
    $summary = $dashboard['summary'];
    $charts = $dashboard['charts'];

    $categoryIcons = [
        'Alimentação' => 'fa-utensils', 'Moradia' => 'fa-house', 'Transporte' => 'fa-car',
        'Saúde' => 'fa-heart-pulse', 'Educação' => 'fa-graduation-cap', 'Lazer' => 'fa-gamepad',
        'Assinaturas' => 'fa-rotate', 'Compras' => 'fa-bag-shopping', 'Impostos' => 'fa-file-invoice-dollar',
        'Dívidas' => 'fa-hand-holding-dollar', 'Salário' => 'fa-briefcase', 'Renda extra' => 'fa-money-bill-wave',
        'Freelance' => 'fa-laptop', 'Rendimentos' => 'fa-chart-line', 'Reembolso' => 'fa-rotate-left',
        'Presente' => 'fa-gift',
    ];

    $maxBar = max(1, ...array_map('floatval', array_merge($charts['income'], $charts['expense'])));
    $bars = collect($charts['labels'])->map(fn ($label, $i) => [
        'label' => $label,
        'rec' => max(4, round(((float) $charts['income'][$i]) / $maxBar * 100)),
        'desp' => max(4, round(((float) $charts['expense'][$i]) / $maxBar * 100)),
    ]);

    $topCategories = collect($charts['expense_categories']['labels'])
        ->map(fn ($label, $i) => ['label' => $label, 'value' => (float) $charts['expense_categories']['values'][$i]])
        ->sortByDesc('value')->values()->take(4);
    $maxCategory = max(1, $topCategories->max('value') ?? 1);

    $subscriptionsTotal = $dashboard['subscriptions']->reduce(fn ($t, $s) => bcadd($t, $s->amount, 2), '0.00');
    $forecastIncomeTotal = collect($dashboard['forecast_months'])->reduce(fn ($t, $m) => bcadd($t, $m['income'], 2), '0.00');
    $forecastExpenseTotal = collect($dashboard['forecast_months'])->reduce(fn ($t, $m) => bcadd($t, $m['expense'], 2), '0.00');
    $forecastResultTotal = bcsub($forecastIncomeTotal, $forecastExpenseTotal, 2);
    $maxForecast = max(1, ...array_map(fn ($m) => abs((float) $m['result']), $dashboard['forecast_months']));

    $cardsOutstandingTotal = collect($dashboard['credit_cards'])->reduce(fn ($t, $c) => bcadd($t, $c['outstanding'], 2), '0.00');
    $cardsLimitTotal = collect($dashboard['credit_cards'])->reduce(fn ($t, $c) => bcadd($t, $c['available_limit'], 2), '0.00');

    $upcomingTotal = $dashboard['upcoming']->reduce(fn ($t, $u) => bcadd($t, $u->amount, 2), '0.00');
@endphp
<x-dashboard-layout title="Visão geral">

<div class="app">

  <aside class="sidebar" data-sidebar data-collapsed="false">

    <div class="sidebar__brand-row">
      <div class="brand-swap">
        <img class="brand-swap__logo" src="{{ asset('design/assets/capi/capi-rosto.png') }}" alt="Capí">
        <button class="brand-swap__btn" type="button" data-sidebar-toggle aria-label="Abrir barra lateral"><i class="fa-solid fa-table-columns"></i></button>
      </div>
      <span class="sidebar__brand-name sidebar__label">financi<span class="brand__name-accent">aí</span></span>
      <button class="sidebar__collapse" type="button" data-sidebar-toggle aria-label="Recolher barra lateral"><i class="fa-solid fa-table-columns"></i></button>
    </div>

    <button class="sidebar__cta" type="button" data-goto="chat" title="Bater papo com o Capí">
      <i class="fa-solid fa-comment-dots sidebar__icon"></i>
      <span class="sidebar__label">Bater papo com o Capí</span>
    </button>

    <nav class="sidebar__nav">
      <button class="nav-item" type="button" data-goto="agentes" title="Agentes">
        <i class="fa-solid fa-user-group sidebar__icon"></i>
        <span class="sidebar__label">Agentes</span>
      </button>
      <button class="nav-item" type="button" data-goto="visao" title="Painel" aria-current="page">
        <i class="fa-solid fa-chart-pie sidebar__icon"></i>
        <span class="sidebar__label">Painel</span>
      </button>
    </nav>

    <div class="sidebar__history">
      <div class="history__head">
        <span class="history__title">Conversas</span>
        <span class="tag-soon">em breve</span>
      </div>
      <div class="history__list history__list--empty">
        <p class="history__empty-text">O histórico de conversas com o Capí chega junto com os agentes.</p>
      </div>
    </div>

    <div class="sidebar__user">
      <span class="avatar">{{ collect(explode(' ', trim($user->name)))->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('') }}</span>
      <span class="sidebar__user-info sidebar__label">
        <span class="sidebar__user-name">{{ $user->name }}</span>
        <span class="sidebar__user-plan">Beta aberto</span>
      </span>
      <a class="sidebar__settings" href="{{ route('settings.edit') }}" aria-label="Configurações"><i class="fa-solid fa-gear"></i></a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="sidebar__settings" type="submit" aria-label="Sair"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
      </form>
    </div>
  </aside>

  <main class="main">

    <header class="topbar">
      <div class="topbar__inner">

        <nav class="tabs-nav">
          <button class="tab-btn" type="button" data-goto="visao" aria-current="page"><i class="fa-solid fa-gauge-high"></i>Visão geral</button>
          <button class="tab-btn" type="button" data-goto="transacoes"><i class="fa-solid fa-arrow-right-arrow-left"></i>Transações</button>
          <button class="tab-btn" type="button" data-goto="assinaturas"><i class="fa-solid fa-rotate"></i>Assinaturas</button>
          <button class="tab-btn" type="button" data-goto="planejamento"><i class="fa-regular fa-calendar-days"></i>Planejamento</button>
          <button class="tab-btn" type="button" data-goto="contas"><i class="fa-solid fa-building-columns"></i>Contas</button>
          <button class="tab-btn" type="button" data-goto="cartoes"><i class="fa-regular fa-credit-card"></i>Cartões</button>
        </nav>

        <div class="topbar__actions">
          <button class="icon-btn" type="button" data-notif-toggle aria-label="Notificações" aria-expanded="false">
            <i class="fa-regular fa-bell"></i>
            @if($summary['overdue_count'] + $summary['overdue_bill_count'] + $summary['upcoming_count'] > 0)
              <span class="icon-btn__dot"></span>
            @endif
          </button>
          <button class="icon-btn" type="button" data-theme-toggle data-toggle-url="{{ route('settings.toggle-theme') }}" aria-label="Alternar tema"><i class="fa-solid {{ $userSettings->theme === 'dark' ? 'fa-sun' : 'fa-moon' }}"></i></button>
          <button class="icon-btn" type="button" data-toggle-money data-toggle-url="{{ route('settings.toggle-values') }}" aria-label="Mostrar ou ocultar valores" aria-pressed="{{ $hide ? 'true' : 'false' }}"><i class="fa-regular {{ $hide ? 'fa-eye-slash' : 'fa-eye' }}"></i></button>
          <a class="btn-add" href="{{ route('transactions.create') }}"><i class="fa-solid fa-plus"></i>Nova transação</a>

          <div class="notif-panel" data-notif-panel hidden>
            <div class="notif-panel__head">
              <span class="notif-panel__title">Notificações</span>
              <span class="notif-panel__count">{{ $summary['overdue_count'] + $summary['overdue_bill_count'] + ($summary['upcoming_count'] > 0 ? 1 : 0) }} pendências</span>
            </div>
            <div>
              @if($summary['overdue_bill_count'] > 0)
                <button class="notif" type="button">
                  <span class="notif__icon notif__icon--fg"><i class="fa-regular fa-credit-card"></i></span>
                  <span class="notif__body">
                    <span class="notif__title">{{ $summary['overdue_bill_count'] }} fatura(s) de cartão vencida(s)</span>
                    <span class="notif__text">Revise em Cartões.</span>
                  </span>
                </button>
              @endif
              @if($summary['overdue_count'] > 0)
                <button class="notif" type="button">
                  <span class="notif__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                  <span class="notif__body">
                    <span class="notif__title">{{ $summary['overdue_count'] }} transação(ões) vencida(s)</span>
                    <span class="notif__text">Revise em Transações.</span>
                  </span>
                </button>
              @endif
              @if($summary['upcoming_count'] > 0)
                <button class="notif" type="button">
                  <span class="notif__icon notif__icon--accent"><i class="fa-regular fa-clock"></i></span>
                  <span class="notif__body">
                    <span class="notif__title">{{ $summary['upcoming_count'] }} compromisso(s) nos próximos 15 dias</span>
                    <span class="notif__text">Veja a lista na Visão geral.</span>
                  </span>
                </button>
              @endif
              @if($summary['overdue_count'] === 0 && $summary['overdue_bill_count'] === 0 && $summary['upcoming_count'] === 0)
                <p style="padding: 16px; font-size: 14px; color: var(--muted);">Nenhuma pendência por aqui.</p>
              @endif
            </div>
            <button class="notif-panel__foot" type="button" data-notif-read>Marcar todas como lidas</button>
          </div>
        </div>
      </div>
    </header>

    <div class="stage">
      <div class="stage__inner">

        <div class="page-head">
          <div>
            <h1 class="page-head__title" data-page-title>Visão geral</h1>
            <p class="page-head__sub" data-page-sub>Seu panorama financeiro, sem ruído.</p>
          </div>

          <div class="period" data-period>
            <div class="period__group" data-period-group>
              <button class="period__btn" type="button" data-period-value="30d" aria-pressed="true">30 dias</button>
              <button class="period__btn" type="button" data-period-value="12m" aria-pressed="false">12 meses</button>
              <button class="period__btn" type="button" data-period-value="intervalo" aria-pressed="false">Intervalo</button>
            </div>
            <div class="date-range" data-date-range hidden>
              <i class="fa-regular fa-calendar"></i>
              <input type="date" value="{{ $dashboard['period']['start']->toDateString() }}" aria-label="Data inicial">
              <span>até</span>
              <input type="date" value="{{ $dashboard['period']['end']->toDateString() }}" aria-label="Data final">
            </div>
            <span class="period__summary" data-period-summary>{{ $dashboard['period']['start']->translatedFormat('j \d\e M') }} – {{ $dashboard['period']['end']->translatedFormat('j \d\e M \d\e Y') }}</span>
          </div>
        </div>

        <!-- ============ Visão geral ============ -->
        <section class="page stack" data-page="visao">

          <div class="grid-3">
            <article class="kpi kpi--hover" data-enter>
              <div class="kpi__head">
                <span class="kpi__label"><i class="fa-solid fa-wallet"></i>Saldo atual</span>
                <span class="kpi__delta kpi__delta--neutral">{{ $dashboard['accounts']->count() }} {{ Str::plural('conta', $dashboard['accounts']->count()) }}</span>
              </div>
              <div class="kpi__value" data-money>{{ Money::format($summary['balance_current'], $hide) }}</div>
              <div class="kpi__note">Somente movimentações efetivadas</div>
            </article>
            <article class="kpi kpi--hover" data-enter>
              <div class="kpi__head">
                <span class="kpi__label"><i class="fa-solid fa-arrow-trend-up"></i>Resultado do mês</span>
                <span class="kpi__delta {{ bccomp($summary['result'], '0', 2) < 0 ? 'kpi__delta--neutral' : '' }}">{{ bccomp($summary['result'], '0', 2) >= 0 ? 'Positivo' : 'Negativo' }}</span>
              </div>
              <div class="kpi__value kpi__value--accent" data-money>{{ Money::format($summary['result'], $hide) }}</div>
              <div class="kpi__note">Receitas menos despesas</div>
            </article>
            <article class="kpi kpi--hover" data-enter>
              <div class="kpi__head">
                <span class="kpi__label"><i class="fa-regular fa-clock"></i>Compromissos futuros</span>
                <span class="kpi__delta kpi__delta--neutral">{{ $summary['upcoming_count'] }} {{ Str::plural('item', $summary['upcoming_count']) }}</span>
              </div>
              <div class="kpi__value" data-money>{{ Money::format($upcomingTotal, $hide) }}</div>
              <div class="kpi__note">Próximos 15 dias</div>
            </article>
          </div>

          <div class="grid-wide">
            <section class="panel panel--span2" data-enter>
              <div class="panel__head">
                <div>
                  <h2 class="panel__title">Entradas e saídas</h2>
                  <p class="panel__sub">Últimos 6 meses</p>
                </div>
                <div class="legend">
                  <span><span class="legend__swatch legend__swatch--rec"></span>Receitas</span>
                  <span><span class="legend__swatch legend__swatch--desp"></span>Despesas</span>
                </div>
              </div>
              <div class="bars">
                @foreach($bars as $bar)
                  <div class="bars__col">
                    <div class="bars__pair"><span class="bar bar--rec" style="height:{{ $bar['rec'] }}%"></span><span class="bar bar--desp" style="height:{{ $bar['desp'] }}%"></span></div>
                    <span class="bars__label">{{ $bar['label'] }}</span>
                  </div>
                @endforeach
              </div>
            </section>

            <section class="panel" data-enter>
              <h2 class="panel__title">Próximos compromissos</h2>
              <p class="panel__sub panel__sub--gap">15 dias</p>
              <div class="due-list">
                @forelse($dashboard['upcoming'] as $item)
                  <div class="due">
                    <span class="due__date"><span class="due__day">{{ $item->due_date->format('d') }}</span><span class="due__month">{{ $item->due_date->translatedFormat('M') }}</span></span>
                    <span class="due__body">
                      <span class="due__name">{{ $item->description }}</span>
                      <span class="due__status {{ $item->type->value === 'income' ? 'due__status--accent' : '' }}">
                        @if($item->installment_total)
                          {{ $item->installment_number }} de {{ $item->installment_total }}
                        @elseif($item->type->value === 'income')
                          Entrada prevista
                        @else
                          {{ $item->category?->name ?? 'Despesa' }}
                        @endif
                      </span>
                    </span>
                    <span class="due__value {{ $item->type->value === 'income' ? 'due__value--accent' : '' }}" data-money>{{ Money::format($item->amount, $hide) }}</span>
                  </div>
                @empty
                  <p style="font-size: 14px; color: var(--muted);">Nenhum compromisso nos próximos 15 dias.</p>
                @endforelse
              </div>
            </section>
          </div>

          <div class="grid-2">
            <section class="panel" data-enter>
              <h2 class="panel__title">Para onde foi o dinheiro</h2>
              <p class="panel__sub panel__sub--gap-lg">{{ $dashboard['period']['start']->translatedFormat('F') }} · principais categorias</p>
              <div class="cat-list">
                @forelse($topCategories as $cat)
                  <div class="cat">
                    <div class="cat__row">
                      <span class="cat__name"><i class="fa-solid {{ $categoryIcons[$cat['label']] ?? 'fa-tag' }}"></i>{{ $cat['label'] }}</span>
                      <span class="cat__value" data-money>{{ Money::format((string) $cat['value'], $hide) }}</span>
                    </div>
                    <div class="track"><span class="track__fill" style="width:{{ max(4, round($cat['value'] / $maxCategory * 100)) }}%"></span></div>
                  </div>
                @empty
                  <p style="font-size: 14px; color: var(--muted);">Nenhuma despesa categorizada neste período.</p>
                @endforelse
              </div>
            </section>

            <section class="capi-card" data-enter>
              <span class="capi-card__ring" aria-hidden="true"></span>
              <div class="capi-card__body">
                <span class="capi-card__eyebrow">Capí sugere</span>
                <p class="capi-card__text">O assistente do Capí ainda está em desenvolvimento. Em breve ele vai analisar seus lançamentos e trazer sugestões por aqui.</p>
              </div>
              <div class="capi-card__foot">
                <button class="btn-capi" type="button" data-goto="chat"><i class="fa-solid fa-comment-dots"></i>Conversar com o Capí</button>
                <img class="capi-card__art" src="{{ asset('design/assets/capi/capi-apontando.png') }}" alt="">
              </div>
            </section>
          </div>
        </section>

        <!-- ============ Transações ============ -->
        <section class="page stack" data-page="transacoes" hidden>

          <div class="filters" data-enter>
            <label class="search-field">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="search" placeholder="Buscar por descrição">
            </label>
            <label class="select-field">
              <i class="fa-solid fa-arrow-right-arrow-left"></i>
              <select aria-label="Tipo de lançamento">
                <option value="todos">Entradas e saídas</option>
                <option value="entradas">Somente entradas</option>
                <option value="saidas">Somente saídas</option>
                <option value="pendentes">Pendentes</option>
              </select>
              <i class="fa-solid fa-chevron-down select-field__chevron"></i>
            </label>
            <a class="select-field" href="{{ route('transactions.index') }}" style="justify-content: center; text-decoration: none;">
              <i class="fa-solid fa-list"></i><span>Ver lista completa e filtrar</span>
            </a>
          </div>

          @forelse($dashboard['transactions_by_day'] as $date => $items)
            <section class="list-card" data-enter>
              @php($dayTotal = $items->reduce(fn ($t, $i) => $i->type->value === 'income' ? bcadd($t, $i->amount, 2) : bcsub($t, $i->amount, 2), '0.00'))
              <div class="list-card__head">
                <span class="list-card__title">{{ \Illuminate\Support\Carbon::parse($date)->isToday() ? 'Hoje · ' : (\Illuminate\Support\Carbon::parse($date)->isYesterday() ? 'Ontem · ' : '') }}{{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('j \d\e F') }}</span>
                <span class="list-card__total" data-money>{{ bccomp($dayTotal, '0', 2) >= 0 ? '+ ' : '− ' }}{{ Money::format(ltrim($dayTotal, '-'), $hide) }}</span>
              </div>
              @foreach($items as $t)
                <div class="row">
                  <span class="row__icon"><i class="fa-solid {{ $categoryIcons[$t->category?->name] ?? ($t->type->value === 'income' ? 'fa-briefcase' : 'fa-receipt') }}"></i></span>
                  <span class="row__body"><span class="row__name">{{ $t->description }}</span><span class="row__detail">{{ $t->category?->name ?? $t->type->label() }} · {{ $t->account?->name }}</span></span>
                  <span class="row__value {{ $t->type->value === 'income' ? 'row__value--accent' : '' }}" data-money>{{ $t->type->value === 'income' ? '+ ' : '− ' }}{{ Money::format($t->amount, $hide) }}</span>
                </div>
              @endforeach
            </section>
          @empty
            <section class="list-card" data-enter>
              <p style="padding: 20px; font-size: 14px; color: var(--muted);">Nenhuma transação efetivada ainda. <a href="{{ route('transactions.create') }}">Registrar a primeira</a>.</p>
            </section>
          @endforelse
        </section>

        <!-- ============ Assinaturas ============ -->
        <section class="page stack" data-page="assinaturas" hidden>

          <div class="grid-3">
            <article class="kpi" data-enter>
              <div class="kpi__label"><i class="fa-solid fa-rotate"></i>Total por mês</div>
              <div class="kpi__value kpi__value--sm" data-money>{{ Money::format($subscriptionsTotal, $hide) }}</div>
              <div class="kpi__note">{{ $dashboard['subscriptions']->count() }} {{ Str::plural('assinatura ativa', $dashboard['subscriptions']->count()) }}</div>
            </article>
            <article class="kpi" data-enter>
              <div class="kpi__label"><i class="fa-solid fa-chart-pie"></i>Peso no orçamento</div>
              <div class="kpi__value kpi__value--sm">{{ str_replace('.', ',', Money::percentage($subscriptionsTotal, $summary['expense'])) }}%</div>
              <div class="kpi__note">Das suas despesas do período</div>
            </article>
            <article class="kpi" data-enter>
              <div class="kpi__label"><i class="fa-regular fa-clock"></i>Próxima cobrança</div>
              @php($next = $dashboard['subscriptions']->sortBy('due_date')->first())
              <div class="kpi__value kpi__value--sm kpi__value--accent">{{ $next?->due_date?->format('d/m') ?? '—' }}</div>
              <div class="kpi__note">{{ $next?->description ?? 'Nenhuma cadastrada' }}</div>
            </article>
          </div>

          <section class="list-card" data-enter>
            <div class="list-card__head list-card__head--tall">
              <span class="list-card__title">Assinaturas identificadas</span>
              <span class="list-card__note">A partir de lançamentos recorrentes</span>
            </div>

            @forelse($dashboard['subscriptions'] as $sub)
              <div class="row row--tall">
                <span class="row__icon row__icon--lg"><i class="fa-solid {{ $categoryIcons[$sub->category?->name] ?? 'fa-rotate' }}"></i></span>
                <span class="row__body"><span class="row__name row__name--bold">{{ $sub->description }}</span><span class="row__detail">{{ $sub->category?->name ?? 'Assinatura' }} · {{ $sub->account?->name }}</span></span>
                <span class="row__right"><span class="row__value" data-money>{{ Money::format($sub->amount, $hide) }}</span><span class="row__next">{{ $sub->due_date?->format('d/m') }}</span></span>
              </div>
            @empty
              <div class="list-card__foot">
                <span>Nenhuma assinatura identificada ainda. Lançamentos recorrentes cadastrados como despesa aparecem aqui automaticamente.</span>
              </div>
            @endforelse
          </section>
        </section>

        <!-- ============ Planejamento ============ -->
        <section class="page stack" data-page="planejamento" hidden>

          <div class="grid-3">
            <article class="kpi" data-enter>
              <div class="kpi__label"><i class="fa-solid fa-arrow-down-long"></i>Receitas previstas</div>
              <div class="kpi__value kpi__value--sm kpi__value--accent" data-money>{{ Money::format($forecastIncomeTotal, $hide) }}</div>
              <div class="kpi__note">Próximos 6 meses</div>
            </article>
            <article class="kpi" data-enter>
              <div class="kpi__label"><i class="fa-solid fa-arrow-up-long"></i>Despesas previstas</div>
              <div class="kpi__value kpi__value--sm" data-money>{{ Money::format($forecastExpenseTotal, $hide) }}</div>
              <div class="kpi__note">Próximos 6 meses</div>
            </article>
            <article class="kpi" data-enter>
              <div class="kpi__label"><i class="fa-solid fa-scale-balanced"></i>Resultado projetado</div>
              <div class="kpi__value kpi__value--sm kpi__value--accent" data-money>{{ Money::format($forecastResultTotal, $hide) }}</div>
              <div class="kpi__note">Se o planejado se confirmar</div>
            </article>
          </div>

          <section class="panel" data-enter>
            <h2 class="panel__title">Próximos 6 meses</h2>
            <p class="panel__sub panel__sub--gap-lg">Resultado previsto por mês, a partir de lançamentos planejados</p>
            <div class="month-list">
              @foreach($dashboard['forecast_months'] as $m)
                <div class="month-row">
                  <span class="month-row__name">{{ $m['month']->translatedFormat('M') }}</span>
                  <span class="track track--tall"><span class="track__fill {{ bccomp($m['result'], '0', 2) < 0 ? 'track__fill--danger' : '' }}" style="width:{{ max(4, round(abs((float) $m['result']) / $maxForecast * 100)) }}%"></span></span>
                  <span class="month-row__value {{ bccomp($m['result'], '0', 2) < 0 ? 'month-row__value--danger' : '' }}" data-money>{{ bccomp($m['result'], '0', 2) >= 0 ? '+ ' : '− ' }}{{ Money::format(ltrim($m['result'], '-'), $hide) }}</span>
                </div>
              @endforeach
            </div>
          </section>
        </section>

        <!-- ============ Contas ============ -->
        <section class="page" data-page="contas" hidden>

          <section class="import-card" data-enter>
            <div class="import-card__head">
              <div>
                <h2 class="panel__title">Trazer seus lançamentos</h2>
                <p class="import-card__text">Baixe o extrato no seu banco e importe aqui. O financiaí lê o arquivo, identifica as categorias e mostra tudo para você conferir antes de salvar.</p>
              </div>
              <span class="chip-shield"><i class="fa-solid fa-shield-halved"></i>Sem acesso ao seu banco</span>
            </div>

            <div class="import-card__body">
              <a class="import-drop" href="{{ route('transactions.import.create') }}">
                <i class="fa-solid fa-file-arrow-up import-drop__icon"></i>
                <span class="import-drop__title">Importar extrato</span>
                <span class="import-drop__hint">OFX ou CSV · até 12 meses por arquivo</span>
                <span class="import-drop__cta">Começar<i class="fa-solid fa-arrow-right"></i></span>
              </a>

              <div class="import-steps">
                <div class="step-mini">
                  <span class="step-mini__num">1</span>
                  <span class="step-mini__body">
                    <span class="step-mini__title">Baixe o extrato no seu banco</span>
                    <span class="step-mini__text">No app do banco, exporte o período em OFX ou CSV.</span>
                  </span>
                </div>
                <div class="step-mini">
                  <span class="step-mini__num">2</span>
                  <span class="step-mini__body">
                    <span class="step-mini__title">Confira o que foi lido</span>
                    <span class="step-mini__text">Datas, valores e categorias sugeridas aparecem para revisão.</span>
                  </span>
                </div>
                <div class="step-mini">
                  <span class="step-mini__num">3</span>
                  <span class="step-mini__body">
                    <span class="step-mini__title">Salve na conta certa</span>
                    <span class="step-mini__text">Lançamentos duplicados são identificados automaticamente.</span>
                  </span>
                </div>
              </div>
            </div>
          </section>

          <div class="grid-accounts">
            @foreach($dashboard['accounts'] as $row)
              <article class="account-card" data-enter>
                <div class="account-card__head">
                  <span class="row__icon row__icon--lg"><i class="fa-solid fa-building-columns"></i></span>
                  <span class="account-card__info">
                    <span class="account-card__name">{{ $row['account']->name }}</span>
                    <span class="account-card__type">{{ $row['account']->type->label() }}</span>
                  </span>
                </div>
                <div class="account-card__balance" data-money>{{ Money::format($row['current'], $hide) }}</div>
                <div class="account-card__note">Saldo calculado a partir das suas movimentações</div>
              </article>
            @endforeach

            <a class="add-card" href="{{ route('accounts.create') }}">
              <i class="fa-solid fa-plus"></i>
              <span>Adicionar conta manual</span>
            </a>
          </div>
        </section>

        <!-- ============ Cartões ============ -->
        <section class="page stack" data-page="cartoes" hidden>

          <div class="grid-3">
            <article class="kpi" data-enter>
              <div class="kpi__label"><i class="fa-regular fa-credit-card"></i>Faturas em aberto</div>
              <div class="kpi__value kpi__value--sm" data-money>{{ Money::format($cardsOutstandingTotal, $hide) }}</div>
              <div class="kpi__note">{{ count($dashboard['credit_cards']) }} {{ Str::plural('cartão ativo', count($dashboard['credit_cards'])) }}</div>
            </article>
            <article class="kpi" data-enter>
              <div class="kpi__label"><i class="fa-solid fa-gauge-high"></i>Limite disponível</div>
              <div class="kpi__value kpi__value--sm kpi__value--accent" data-money>{{ Money::format($cardsLimitTotal, $hide) }}</div>
              <div class="kpi__note">Somado entre os cartões ativos</div>
            </article>
          </div>

          <div class="grid-cards">
            @forelse($dashboard['credit_cards'] as $row)
              <article class="credit-card" data-enter>
                <div class="credit-card__top">
                  <div class="credit-card__brand-row">
                    <span class="credit-card__brand"><i class="fa-regular fa-credit-card"></i>{{ $row['card']->issuer }}</span>
                    <span class="credit-card__state">Ativo</span>
                  </div>
                  <div class="credit-card__name">{{ $row['card']->name }}</div>
                </div>
                <div class="credit-card__body">
                  <div class="credit-card__figures">
                    <span class="credit-card__figure">
                      <span class="credit-card__figure-label">Fatura atual</span>
                      <span class="credit-card__figure-value" data-money>{{ Money::format($row['outstanding'], $hide) }}</span>
                    </span>
                    <span class="credit-card__figure credit-card__figure--right">
                      <span class="credit-card__figure-label">Vence</span>
                      <span class="credit-card__figure-value credit-card__figure-value--sm">{{ $row['next_bill']?->due_date?->format('d/m') ?? '—' }}</span>
                    </span>
                  </div>
                  <div class="credit-card__limit">
                    <div class="credit-card__limit-row"><span>Limite usado</span><span>{{ str_replace('.', ',', $row['limit_used_pct']) }}%</span></div>
                    <div class="track"><span class="track__fill" style="width:{{ min(100, $row['limit_used_pct']) }}%"></span></div>
                  </div>
                </div>
              </article>
            @empty
              <p style="font-size: 14px; color: var(--muted);">Nenhum cartão cadastrado. <a href="{{ route('credit-cards.create') }}">Adicionar cartão</a>.</p>
            @endforelse
          </div>
        </section>

        <!-- ============ Capí (chat) ============ -->
        <section class="page" data-page="chat" hidden>
          <div class="chat" data-enter>
            <img class="chat__capi" src="{{ asset('design/assets/capi/capi-parado.png') }}" alt="Capí">
            <h2 class="chat__title">No que posso ajudar hoje?</h2>
            <p class="chat__text">Pergunte sobre seus gastos, metas ou os próximos meses. O Capí responde com base nos seus próprios registros.</p>

            <div class="composer">
              <textarea rows="1" placeholder="Pergunte ao Capí…"></textarea>
              <button class="composer__send" type="button" aria-label="Enviar"><i class="fa-solid fa-arrow-up"></i></button>
            </div>

            <div class="chat__chips">
              <button class="chip" type="button">Quanto gastei este mês?</button>
              <button class="chip" type="button">Onde posso economizar?</button>
              <button class="chip" type="button">Consigo viajar em dezembro?</button>
              <button class="chip" type="button">Como está minha reserva?</button>
            </div>

            <p class="chat__disclaimer">O assistente ainda está em desenvolvimento. Nenhuma resposta é gerada hoje.</p>
          </div>
        </section>

        <!-- ============ Agentes ============ -->
        <section class="page" data-page="agentes" hidden>

          <section class="agents-hero" data-enter>
            <span class="agents-hero__ring" aria-hidden="true"></span>
            <div class="agents-hero__body">
              <span class="agents-hero__badge"><i class="fa-solid fa-lightbulb"></i>Bízu do Capí</span>
              <h2 class="agents-hero__title">Esses são os agentes recomendados pelo próprio Capí.</h2>
              <p class="agents-hero__text">Cada um cuida de uma área específica da sua vida financeira. Comece pelo que mais pesa no seu mês — o Capí te avisa quando fizer sentido acionar os outros.</p>
            </div>
            <img class="agents-hero__art" src="{{ asset('design/assets/capi/capi-apontando.png') }}" alt="">
          </section>

          <div class="grid-3">
            <article class="agent-card agent-card--dumont" data-enter>
              <div class="agent-card__art"><img src="{{ asset('design/assets/agentes/dumont.png') }}" alt="Dumont"></div>
              <div class="agent-card__body">
                <div class="agent-card__head">
                  <span class="agent-card__accent"></span>
                  <h3 class="agent-card__name">Dumont</h3>
                  <span class="agent-card__soon">em breve</span>
                </div>
                <div class="agent-card__role">Viagens e planejamento</div>
                <p class="agent-card__text">Viagens e planos que cabem no seu orçamento.</p>
              </div>
            </article>

            <article class="agent-card agent-card--dinho" data-enter>
              <div class="agent-card__art"><img src="{{ asset('design/assets/agentes/dinho.png') }}" alt="Dinho"></div>
              <div class="agent-card__body">
                <div class="agent-card__head">
                  <span class="agent-card__accent"></span>
                  <h3 class="agent-card__name">Dinho</h3>
                  <span class="agent-card__soon">em breve</span>
                </div>
                <div class="agent-card__role">Economia do dia a dia</div>
                <p class="agent-card__text">Rolês e gastos do dia a dia equilibrados até o fim do mês.</p>
              </div>
            </article>

            <article class="agent-card agent-card--poatan" data-enter>
              <div class="agent-card__art"><img src="{{ asset('design/assets/agentes/saude.png') }}" alt="Poatan"></div>
              <div class="agent-card__body">
                <div class="agent-card__head">
                  <span class="agent-card__accent"></span>
                  <h3 class="agent-card__name">Poatan</h3>
                  <span class="agent-card__soon">em breve</span>
                </div>
                <div class="agent-card__role">Rotina e disciplina</div>
                <p class="agent-card__text">Rotina que cabe na semana e no bolso. Sem cobrança.</p>
              </div>
            </article>
          </div>
        </section>

      </div>
    </div>
  </main>
</div>

</x-dashboard-layout>
