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

    $initials = collect(explode(' ', trim($user->name)))->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('');
    $transactionsInPeriod = $user->transactions()->whereBetween('competence_date', [$dashboard['period']['start'], $dashboard['period']['end']])->count();
    $activeSessionsCount = \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', $user->id)->count();

    $monthStartDayOptions = collect([1, 5, 10, 15, 20]);
    if (! $monthStartDayOptions->contains($userSettings->financial_month_start_day)) {
        $monthStartDayOptions = $monthStartDayOptions->push($userSettings->financial_month_start_day)->sort()->values();
    }
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

    <div class="sidebar__user" data-user-menu>
      <div class="sidebar__user-row">
        <button class="sidebar__user-btn" type="button" aria-label="Sua conta" aria-expanded="false" data-user-toggle>
          <span class="avatar">@if($user->avatarUrl())<img src="{{ $user->avatarUrl() }}" alt="">@else{{ $initials }}@endif</span>
          <span class="sidebar__user-info sidebar__label">
            <span class="sidebar__user-name">{{ $user->name }}</span>
            <span class="sidebar__user-plan">Beta aberto</span>
          </span>
        </button>
        <button class="sidebar__settings" type="button" aria-label="Configurações" data-goto="config"><i class="fa-solid fa-gear"></i></button>
      </div>

      <div class="user-card" hidden data-user-card>
        <div class="user-card__head">
          <span class="avatar avatar--lg">@if($user->avatarUrl())<img src="{{ $user->avatarUrl() }}" alt="">@else{{ $initials }}@endif</span>
          <span class="user-card__ident">
            <span class="user-card__name">{{ $user->name }}</span>
            <span class="user-card__mail">{{ $user->email }}</span>
          </span>
        </div>

        <div class="user-card__plan">
          <span class="user-card__plan-info">
            <span class="user-card__plan-name">Beta aberto</span>
          </span>
        </div>

        <div class="user-card__stats">
          <span class="user-card__stat"><strong>{{ $dashboard['accounts']->count() }}</strong>{{ $dashboard['accounts']->count() === 1 ? 'conta conectada' : 'contas conectadas' }}</span>
          <span class="user-card__stat"><strong>{{ $transactionsInPeriod }}</strong>lançamentos no período</span>
        </div>

        <div class="user-card__actions">
          <button class="user-card__action" type="button" data-goto="config"><i class="fa-solid fa-gear"></i>Configurações</button>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="user-card__action user-card__action--out" type="submit"><i class="fa-solid fa-arrow-right-from-bracket"></i>Sair da conta</button>
          </form>
        </div>
      </div>
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
          <button class="btn-add" type="button" data-modal-open="transacao"><i class="fa-solid fa-plus"></i>Nova transação</button>

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
              <x-datepicker name="period_start" :value="$dashboard['period']['start']->toDateString()" label="Data inicial" inline bare />
              <span>até</span>
              <x-datepicker name="period_end" :value="$dashboard['period']['end']->toDateString()" label="Data final" inline right bare />
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
            <x-dropdown name="_tipo_lancamento_display" icon="fa-solid fa-arrow-right-arrow-left" :block="false" :options="[
                ['value' => 'todos', 'label' => 'Entradas e saídas'],
                ['value' => 'entradas', 'label' => 'Somente entradas'],
                ['value' => 'saidas', 'label' => 'Somente saídas'],
                ['value' => 'pendentes', 'label' => 'Pendentes'],
            ]" />
            <x-dropdown name="_categoria_display" icon="fa-solid fa-tag" :block="false" :options="collect([['value' => '', 'label' => 'Todas as categorias']])->concat($categories->map(fn ($category) => ['value' => $category->id, 'label' => $category->name]))" />
          </div>

          @forelse($dashboard['transactions_by_day'] as $date => $items)
            <section class="list-card" data-enter>
              @php
                $dayTotal = $items->reduce(fn ($t, $i) => $i->type->value === 'income' ? bcadd($t, $i->amount, 2) : bcsub($t, $i->amount, 2), '0.00');
              @endphp
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
            <section class="empty-state" data-enter>
              <p>Nenhuma transação efetivada ainda. <button class="link-btn" type="button" data-modal-open="transacao">Registrar a primeira.</button></p>
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
              @php
                $next = $dashboard['subscriptions']->sortBy('due_date')->first();
              @endphp
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
              <div class="row row--tall">
                <span class="row__body"><span class="row__name row__name--bold">Nenhuma assinatura identificada ainda.</span><span class="row__detail">Lançamentos recorrentes cadastrados como despesa aparecem aqui automaticamente.</span></span>
              </div>
            @endforelse

            <div class="list-card__foot">
              <span>Cancelar uma assinatura pouco usada é o ajuste mais rápido no seu mês.</span>
              <button class="btn-outline" type="button" data-modal-open="assinatura"><i class="fa-solid fa-plus"></i>Adicionar assinatura</button>
            </div>
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

            <button class="add-card" type="button" data-goto="novaConta">
              <i class="fa-solid fa-plus"></i>
              <span>Adicionar conta manual</span>
            </button>
          </div>
        </section>

        <!-- ============ Cartões ============ -->
        <section class="page stack" data-page="cartoes" hidden>

          @if(count($dashboard['credit_cards']) > 0)
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
                @foreach($dashboard['credit_cards'] as $row)
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
                @endforeach

                <button class="add-card add-card--tall" type="button" data-modal-open="cartao">
                  <i class="fa-solid fa-plus"></i>
                  <span>Adicionar cartão</span>
                </button>
              </div>
          @else
              <section class="empty-state" data-enter>
                <p>Nenhum cartão cadastrado. <button class="link-btn" type="button" data-modal-open="cartao">Adicionar cartão.</button></p>
              </section>
          @endif
        </section>

        <!-- ============ Nova conta manual ============ -->
        <section class="page" data-page="novaConta" hidden>
          <form class="new-account" data-enter method="POST" action="{{ route('accounts.store') }}">
            @csrf

            <section class="panel new-account__form">
              <div>
                <h2 class="panel__title">Dados da conta</h2>
                <p class="panel__sub">Contas manuais servem para dinheiro que não vem de extrato: carteira, cofrinho, conta de outro banco. Você registra o saldo de partida e segue lançando por aqui.</p>
              </div>

              <div class="field">
                <span class="field__label">Tipo de conta</span>
                <div class="option-grid" data-account-type>
                  @foreach($accountTypeTiles as $tile)
                    <button class="option-tile {{ $loop->first ? 'is-selected' : '' }}" type="button" data-value="{{ $tile['key'] }}" data-type="{{ $tile['type'] }}" data-icon-key="{{ $tile['icon'] }}" data-icon="{{ $tile['iconClass'] }}"><i class="{{ $tile['iconClass'] }}"></i>{{ $tile['label'] }}</button>
                  @endforeach
                </div>
                <input type="hidden" name="type" value="{{ $accountTypeTiles[0]['type'] }}" id="novaConta-type">
                <input type="hidden" name="icon" value="{{ $accountTypeTiles[0]['icon'] }}" id="novaConta-icon">
                @error('type')<span class="field-error">{{ $message }}</span>@enderror
              </div>

              <div class="field-row">
                <label class="field">
                  <span class="field__label">Nome da conta</span>
                  <input class="input" type="text" name="name" placeholder="Ex.: Carteira do dia a dia" value="{{ old('name') }}" data-account-name required>
                  @error('name')<span class="field-error">{{ $message }}</span>@enderror
                </label>
                <label class="field">
                  <span class="field__label">Instituição <span class="field__hint">· opcional</span></span>
                  <input class="input" type="text" name="institution" placeholder="Ex.: Nubank, Caixa, dinheiro em espécie" value="{{ old('institution') }}" data-account-bank>
                </label>
              </div>

              <div class="field-row">
                <label class="field">
                  <span class="field__label">Saldo de partida</span>
                  <span class="input-group">
                    <span class="input-group__prefix">R$</span>
                    <input class="input-group__input input-group__input--num" type="text" inputmode="decimal" name="initial_balance" placeholder="0,00" value="{{ old('initial_balance') }}" data-account-balance>
                  </span>
                  @error('initial_balance')<span class="field-error">{{ $message }}</span>@enderror
                </label>
                <div class="field">
                  <span class="field__label">Saldo nesta data</span>
                  <x-datepicker name="initial_balance_date" :value="old('initial_balance_date', now()->toDateString())" data-account-date />
                  @error('initial_balance_date')<span class="field-error">{{ $message }}</span>@enderror
                </div>
              </div>

              <div class="field">
                <span class="field__label">Cor da conta</span>
                <div class="swatches" data-account-colors>
                  @foreach(['#137A4A' => 'Verde escuro', '#38C172' => 'Verde', '#2F6FEB' => 'Azul', '#E0A21C' => 'Amarelo', '#B3261E' => 'Vermelho', '#6C4BD6' => 'Roxo'] as $hex => $label)
                    <button class="swatch {{ $loop->first ? 'is-selected' : '' }}" type="button" style="--swatch:{{ $hex }}" data-value="{{ $hex }}" aria-label="{{ $label }}"><i class="fa-solid fa-check"></i></button>
                  @endforeach
                </div>
                <input type="hidden" name="color" value="#137A4A" id="novaConta-color">
                @error('color')<span class="field-error">{{ $message }}</span>@enderror
              </div>

              <div class="switch-row">
                <button class="switch is-on" type="button" role="switch" aria-checked="true" data-account-total><span class="switch__pin"></span></button>
                <span class="switch-row__body">
                  <span class="switch-row__title">Somar no saldo total</span>
                  <span class="switch-row__text">Deixe desligado para acompanhar a conta separadamente, sem afetar os números do painel.</span>
                </span>
              </div>
              <input type="hidden" name="active" value="1" id="novaConta-active">
              <input type="hidden" name="currency" value="BRL">

              <label class="field">
                <span class="field__label">Observação <span class="field__hint">· opcional</span></span>
                <textarea class="input input--area" rows="2" name="notes" placeholder="Para que serve essa conta">{{ old('notes') }}</textarea>
              </label>

              <div class="form-foot">
                <button class="btn-ghost" type="button" data-goto="contas">Cancelar</button>
                <button class="btn-primary btn-primary--sm" type="submit"><i class="fa-solid fa-check"></i>Salvar conta</button>
              </div>
            </section>

            <div class="new-account__aside">
              <article class="panel preview-card">
                <span class="preview-card__label">Prévia</span>
                <div class="account-card__head preview-card__head">
                  <span class="row__icon row__icon--lg preview-card__icon" data-preview-icon><i class="{{ $accountTypeTiles[0]['iconClass'] }}"></i></span>
                  <span class="account-card__info">
                    <span class="account-card__name" data-preview-name>Nome da conta</span>
                    <span class="account-card__type" data-preview-type>{{ $accountTypeTiles[0]['label'] }}</span>
                  </span>
                </div>
                <div class="account-card__balance" data-preview-balance>R$ 0,00</div>
                <div class="account-card__note" data-preview-note>Saldo informado em {{ now()->format('d/m/Y') }}</div>
              </article>

              <article class="tip-card">
                <span class="tip-card__title"><img src="{{ asset('design/assets/capi/capi-rosto.png') }}" alt="">Dica do Capí</span>
                <p class="tip-card__text">Use o saldo que está na conta hoje, não o do começo do mês. A partir dessa data eu passo a somar e subtrair só o que você lançar.</p>
                <a class="tip-card__link" href="{{ route('transactions.import.create') }}">Prefere importar um extrato?<i class="fa-solid fa-arrow-right"></i></a>
              </article>
            </div>
          </form>
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

        <!-- ============ Configurações ============ -->
        <section class="page" data-page="config" hidden>
          <div class="settings" data-enter>

            <nav class="settings__nav">
              <a href="#cfg-perfil"><i class="fa-regular fa-user"></i>Perfil</a>
              <a href="#cfg-preferencias"><i class="fa-solid fa-sliders"></i>Preferências</a>
              <a href="#cfg-notificacoes"><i class="fa-regular fa-bell"></i>Notificações</a>
              <a href="#cfg-seguranca"><i class="fa-solid fa-shield-halved"></i>Segurança</a>
              <a href="#cfg-conta"><i class="fa-solid fa-gear"></i>Conta</a>
            </nav>

            <div class="settings__body">

              <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <section class="panel settings__block" id="cfg-perfil">
                  <div>
                    <h2 class="settings__title">Perfil</h2>
                    <p class="settings__sub">Como você aparece no financiaí e para onde mandamos os avisos.</p>
                  </div>
                  <div class="field">
                    <span class="field__label">Foto de perfil</span>
                    <div class="avatar-upload" id="avatarUpload">
                      <span class="avatar avatar--xl avatar-upload__preview">
                        <span class="avatar-upload__initials" @if($user->avatarUrl()) hidden @endif>{{ $initials }}</span>
                        <img class="avatar-upload__img" id="avatarPreview" alt="Foto de perfil" src="{{ $user->avatarUrl() }}" @unless($user->avatarUrl()) hidden @endunless>
                      </span>
                      <div class="avatar-upload__text">
                        <span class="avatar-upload__name" id="avatarName">{{ $user->avatarUrl() ? 'Foto atual' : 'Nenhuma foto enviada' }}</span>
                        <span class="avatar-upload__hint">Arraste um arquivo aqui ou escolha do computador. JPG ou PNG, até 5 MB.</span>
                      </div>
                      <div class="avatar-upload__actions">
                        <button class="btn-outline" type="button" id="avatarPick">Escolher arquivo</button>
                        <button class="avatar-upload__remove" type="button" id="avatarRemove" @unless($user->avatarUrl()) hidden @endunless>Remover</button>
                      </div>
                      <input class="avatar-upload__input" type="file" id="avatarInput" name="avatar" accept="image/png, image/jpeg">
                    </div>
                    <span class="avatar-upload__error" id="avatarError" hidden></span>
                    <input type="hidden" name="remove_avatar" id="removeAvatarFlag" value="0">
                    @error('avatar')<span class="field-error">{{ $message }}</span>@enderror
                  </div>
                  <div class="field-row">
                    <label class="field">
                      <span class="field__label">Nome</span>
                      <input class="input" type="text" name="name" value="{{ old('name', $user->name) }}">
                      @error('name')<span class="field-error">{{ $message }}</span>@enderror
                    </label>
                    <label class="field">
                      <span class="field__label">E-mail</span>
                      <input class="input" type="email" name="email" value="{{ old('email', $user->email) }}">
                      @error('email')<span class="field-error">{{ $message }}</span>@enderror
                    </label>
                  </div>
                  <div class="form-foot form-foot--bare">
                    <button class="btn-primary btn-primary--sm" type="submit"><i class="fa-solid fa-check"></i>Salvar perfil</button>
                  </div>
                </section>
              </form>

              <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="timezone" value="{{ $userSettings->timezone }}">
                <input type="hidden" name="view_preference" value="{{ $userSettings->view_preference }}">
                <input type="hidden" name="hide_values" value="{{ $userSettings->hide_values ? '1' : '0' }}">
                <input type="hidden" name="confirm_deletion" value="{{ $userSettings->confirm_deletion ? '1' : '0' }}">
                <section class="panel settings__block" id="cfg-preferencias">
                  <div>
                    <h2 class="settings__title">Preferências</h2>
                    <p class="settings__sub">Ajustes que mudam o jeito que o painel se comporta.</p>
                  </div>
                  <div class="field">
                    <span class="field__label">Tema</span>
                    <div class="chip-row" data-chip-group>
                      <button class="chip {{ $userSettings->theme === 'light' ? 'is-selected' : '' }}" type="button" data-theme-set="light"><i class="fa-solid fa-sun"></i>Claro</button>
                      <button class="chip {{ $userSettings->theme === 'dark' ? 'is-selected' : '' }}" type="button" data-theme-set="dark"><i class="fa-solid fa-moon"></i>Escuro</button>
                    </div>
                    <input type="hidden" name="theme" value="{{ $userSettings->theme }}" data-theme-input>
                  </div>
                  <div class="field-row">
                    <div class="field">
                      <span class="field__label">Moeda</span>
                      <x-dropdown name="currency" icon="fa-solid fa-coins" :selected="$userSettings->currency" :options="[
                          ['value' => 'BRL', 'label' => 'Real (R$)'],
                          ['value' => 'USD', 'label' => 'Dólar (US$)'],
                          ['value' => 'EUR', 'label' => 'Euro (€)'],
                      ]" />
                    </div>
                    <div class="field">
                      <span class="field__label">Início do mês financeiro</span>
                      <x-dropdown name="financial_month_start_day" icon="fa-regular fa-calendar" :selected="$userSettings->financial_month_start_day" :options="$monthStartDayOptions->map(fn ($day) => ['value' => $day, 'label' => 'Dia '.$day])" />
                    </div>
                  </div>
                  <div class="form-foot form-foot--bare">
                    <button class="btn-primary btn-primary--sm" type="submit"><i class="fa-solid fa-check"></i>Salvar preferências</button>
                  </div>
                </section>
              </form>

              <section class="panel settings__block" id="cfg-notificacoes">
                <div>
                  <h2 class="settings__title">Notificações<span class="tag-soon">em breve</span></h2>
                  <p class="settings__sub">Escolha o que vale um aviso e o que pode esperar você abrir o painel.</p>
                </div>
                <div class="settings__list">
                  <div class="switch-row">
                    <button class="switch is-on" type="button" role="switch" aria-checked="true"><span class="switch__pin"></span></button>
                    <span class="switch-row__body">
                      <span class="switch-row__title">Fatura fechando</span>
                      <span class="switch-row__text">Três dias antes do fechamento de cada cartão.</span>
                    </span>
                  </div>
                  <div class="switch-row">
                    <button class="switch" type="button" role="switch" aria-checked="false"><span class="switch__pin"></span></button>
                    <span class="switch-row__body">
                      <span class="switch-row__title">Saldo baixo</span>
                      <span class="switch-row__text">Quando alguma conta fica abaixo do limite que você definiu.</span>
                    </span>
                  </div>
                  <div class="switch-row">
                    <button class="switch is-on" type="button" role="switch" aria-checked="true"><span class="switch__pin"></span></button>
                    <span class="switch-row__body">
                      <span class="switch-row__title">Assinatura renovando</span>
                      <span class="switch-row__text">Aviso na véspera da cobrança recorrente.</span>
                    </span>
                  </div>
                  <div class="switch-row">
                    <button class="switch" type="button" role="switch" aria-checked="false"><span class="switch__pin"></span></button>
                    <span class="switch-row__body">
                      <span class="switch-row__title">Resumo semanal</span>
                      <span class="switch-row__text">Um panorama do que entrou e saiu, toda segunda.</span>
                    </span>
                  </div>
                </div>
              </section>

              <section class="panel settings__block" id="cfg-seguranca">
                <div>
                  <h2 class="settings__title">Segurança</h2>
                  <p class="settings__sub">Sua senha e os dispositivos com acesso à conta.</p>
                </div>
                <div class="settings__list">
                  <div class="settings__row">
                    <span class="settings__row-body">
                      <span class="settings__row-title">Senha</span>
                    </span>
                    <button class="btn-outline btn-outline--sm" type="button" data-modal-open="senha">Alterar senha</button>
                  </div>
                  <div class="settings__row">
                    <span class="settings__row-body">
                      <span class="settings__row-title">Sessões ativas</span>
                      <span class="settings__row-text">{{ $activeSessionsCount }} {{ $activeSessionsCount === 1 ? 'dispositivo conectado' : 'dispositivos conectados' }}</span>
                    </span>
                    <button class="btn-ghost" type="button" data-modal-open="encerrarSessoes">Encerrar as outras</button>
                  </div>
                </div>
              </section>

              <section class="panel settings__block" id="cfg-conta">
                <div>
                  <h2 class="settings__title">Conta</h2>
                  <p class="settings__sub">Exportar o que é seu ou encerrar de vez.</p>
                </div>
                <div class="settings__list">
                  <div class="settings__row">
                    <span class="settings__row-body">
                      <span class="settings__row-title">Exportar dados</span>
                      <span class="settings__row-text">Todos os lançamentos em CSV</span>
                    </span>
                    <a class="btn-outline btn-outline--sm" href="{{ route('transactions.export') }}"><i class="fa-solid fa-download"></i>Baixar CSV</a>
                  </div>
                  <div class="settings__row settings__row--danger">
                    <span class="settings__row-body">
                      <span class="settings__row-title">Encerrar conta</span>
                      <span class="settings__row-text">Apaga tudo. Não dá para desfazer.</span>
                    </span>
                    <button class="btn-danger" type="button" data-modal-open="encerrarConta">Encerrar</button>
                  </div>
                </div>
              </section>

            </div>
          </div>
        </section>

      </div>
    </div>
  </main>
</div>

@php
    $defaultAccountId = $dashboard['accounts']->first()['account']->id ?? null;
@endphp

<!-- ============ Modal: nova transação ============ -->
<div class="modal" data-modal="transacao" hidden>
  <div class="modal__veil" data-modal-close></div>
  <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="modal-transacao-titulo">
    <form method="POST" action="{{ route('transactions.store') }}">
      @csrf
      <div class="modal__head">
        <div>
          <h2 class="modal__title" id="modal-transacao-titulo">Nova transação</h2>
          <p class="modal__sub">Um lançamento avulso, que não veio de extrato.</p>
        </div>
        <button class="modal__close" type="button" aria-label="Fechar" data-modal-close><i class="fa-solid fa-xmark"></i></button>
      </div>

      <div class="modal__body">
        <div class="field">
          <span class="field__label">Tipo</span>
          <div class="chip-row" data-chip-group>
            <button class="chip" type="button" data-value="income"><i class="fa-solid fa-arrow-down-long"></i>Entrada</button>
            <button class="chip is-selected" type="button" data-value="expense"><i class="fa-solid fa-arrow-up-long"></i>Saída</button>
            <button class="chip" type="button" data-value="transfer"><i class="fa-solid fa-arrow-right-arrow-left"></i>Transferência</button>
          </div>
          <input type="hidden" name="type" value="expense" data-chip-input>
        </div>

        <div class="field-row">
          <label class="field">
            <span class="field__label">Valor</span>
            <span class="input-group">
              <span class="input-group__prefix">R$</span>
              <input class="input-group__input input-group__input--num" type="text" inputmode="decimal" name="amount" placeholder="0,00" required>
            </span>
            @error('amount')<span class="field-error">{{ $message }}</span>@enderror
          </label>
          <div class="field">
            <span class="field__label">Data</span>
            <x-datepicker name="competence_date" :value="now()->toDateString()" />
          </div>
        </div>

        <label class="field">
          <span class="field__label">Descrição</span>
          <input class="input" type="text" name="description" placeholder="Ex.: Mercado da esquina" required>
          @error('description')<span class="field-error">{{ $message }}</span>@enderror
        </label>

        <div class="field-row">
          <div class="field">
            <span class="field__label">Categoria</span>
            <x-dropdown name="category_id" icon="fa-solid fa-tag" up
                :options="$categories->map(fn ($c) => ['value' => $c->id, 'label' => $c->name])" />
            @error('category_id')<span class="field-error">{{ $message }}</span>@enderror
          </div>
          <div class="field">
            <span class="field__label">Conta ou cartão</span>
            <div class="dropdown dropdown--block dropdown--up" data-dropdown data-account-or-card>
              <button class="dropdown__btn" type="button" aria-haspopup="listbox" aria-expanded="false" data-dropdown-btn>
                <i class="fa-solid fa-building-columns"></i>
                <span data-dropdown-label>{{ $dashboard['accounts']->first()['account']->name ?? 'Selecione' }}</span>
                <i class="fa-solid fa-chevron-down dropdown__chevron"></i>
              </button>
              <div class="dropdown__menu" role="listbox" hidden>
                @foreach($dashboard['accounts'] as $row)
                  <button class="dropdown__opt {{ $loop->first ? 'is-selected' : '' }}" type="button" role="option" data-account-id="{{ $row['account']->id }}">{{ $row['account']->name }}<i class="fa-solid fa-check"></i></button>
                @endforeach
                @foreach($dashboard['credit_cards'] as $row)
                  <button class="dropdown__opt" type="button" role="option" data-credit-card-id="{{ $row['card']->id }}">{{ $row['card']->name }} (cartão)<i class="fa-solid fa-check"></i></button>
                @endforeach
              </div>
            </div>
            <input type="hidden" name="payment_channel" value="account" data-payment-channel>
            <input type="hidden" name="account_id" value="{{ $defaultAccountId }}" data-account-id-field>
            <input type="hidden" name="credit_card_id" value="" data-credit-card-id-field>
            @error('account_id')<span class="field-error">{{ $message }}</span>@enderror
            @error('credit_card_id')<span class="field-error">{{ $message }}</span>@enderror
          </div>
        </div>
        <input type="hidden" name="status" value="completed">
      </div>

      <div class="modal__foot">
        <button class="btn-ghost" type="button" data-modal-close>Cancelar</button>
        <button class="btn-primary btn-primary--sm" type="submit"><i class="fa-solid fa-check"></i>Salvar transação</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ Modal: novo cartão ============ -->
<div class="modal" data-modal="cartao" hidden>
  <div class="modal__veil" data-modal-close></div>
  <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="modal-cartao-titulo">
    <form method="POST" action="{{ route('credit-cards.store') }}">
      @csrf
      <div class="modal__head">
        <div>
          <h2 class="modal__title" id="modal-cartao-titulo">Novo cartão</h2>
          <p class="modal__sub">Só o essencial para calcular fatura e limite. Os lançamentos entram depois.</p>
        </div>
        <button class="modal__close" type="button" aria-label="Fechar" data-modal-close><i class="fa-solid fa-xmark"></i></button>
      </div>

      <div class="modal__body">
        <div class="field">
          <span class="field__label">Bandeira</span>
          <div class="chip-row" data-chip-group>
            <button class="chip is-selected" type="button" data-value="Visa">Visa</button>
            <button class="chip" type="button" data-value="Mastercard">Mastercard</button>
            <button class="chip" type="button" data-value="Elo">Elo</button>
            <button class="chip" type="button" data-value="Amex">Amex</button>
            <button class="chip" type="button" data-value="Outra">Outra</button>
          </div>
          <input type="hidden" name="issuer" value="Visa" data-chip-input>
          @error('issuer')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <label class="field">
          <span class="field__label">Apelido do cartão</span>
          <input class="input" type="text" name="name" placeholder="Ex.: Cartão do mercado" required>
          @error('name')<span class="field-error">{{ $message }}</span>@enderror
        </label>

        <div class="field-row field-row--3">
          <label class="field">
            <span class="field__label">Limite</span>
            <span class="input-group">
              <span class="input-group__prefix">R$</span>
              <input class="input-group__input input-group__input--num" type="text" inputmode="decimal" name="credit_limit" placeholder="0,00" required>
            </span>
            @error('credit_limit')<span class="field-error">{{ $message }}</span>@enderror
          </label>
          <label class="field">
            <span class="field__label">Fecha no dia</span>
            <input class="input input--num" type="number" min="1" max="31" name="closing_day" value="28">
          </label>
          <label class="field">
            <span class="field__label">Vence no dia</span>
            <input class="input input--num" type="number" min="1" max="31" name="due_day" value="5">
          </label>
        </div>

        <input type="hidden" name="color" value="#137A4A">

        <div class="field">
          <span class="field__label">Conta que paga a fatura <span class="field__hint">· opcional</span></span>
          <x-dropdown name="_payer_account_display" icon="fa-solid fa-building-columns" up
              :options="$dashboard['accounts']->map(fn ($row) => ['value' => $row['account']->id, 'label' => $row['account']->name])" />
        </div>
      </div>

      <div class="modal__foot">
        <button class="btn-ghost" type="button" data-modal-close>Cancelar</button>
        <button class="btn-primary btn-primary--sm" type="submit"><i class="fa-solid fa-check"></i>Salvar cartão</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ Modal: nova assinatura ============ -->
<div class="modal" data-modal="assinatura" hidden>
  <div class="modal__veil" data-modal-close></div>
  <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="modal-assinatura-titulo">
    <form method="POST" action="{{ route('transactions.store') }}">
      @csrf
      <input type="hidden" name="type" value="expense">
      <input type="hidden" name="status" value="planned">
      <input type="hidden" name="payment_channel" value="account">
      <input type="hidden" name="account_id" value="{{ $defaultAccountId }}">
      <input type="hidden" name="recurrence_count" value="12">

      <div class="modal__head">
        <div>
          <h2 class="modal__title" id="modal-assinatura-titulo">Nova assinatura</h2>
          <p class="modal__sub">Cadastre uma cobrança que se repete, para ela aparecer nos próximos meses.</p>
        </div>
        <button class="modal__close" type="button" aria-label="Fechar" data-modal-close><i class="fa-solid fa-xmark"></i></button>
      </div>

      <div class="modal__body">
        <label class="field">
          <span class="field__label">Nome do serviço</span>
          <input class="input" type="text" name="description" placeholder="Ex.: Streaming, academia, nuvem" required>
          @error('description')<span class="field-error">{{ $message }}</span>@enderror
        </label>

        <div class="field-row">
          <label class="field">
            <span class="field__label">Valor</span>
            <span class="input-group">
              <span class="input-group__prefix">R$</span>
              <input class="input-group__input input-group__input--num" type="text" inputmode="decimal" name="amount" placeholder="0,00" required>
            </span>
            @error('amount')<span class="field-error">{{ $message }}</span>@enderror
          </label>
          <div class="field">
            <span class="field__label">Próxima cobrança</span>
            <x-datepicker name="competence_date" :value="now()->addMonth()->startOfMonth()->toDateString()" />
          </div>
        </div>

        <div class="field">
          <span class="field__label">Repete a cada</span>
          <div class="chip-row" data-chip-group>
            <button class="chip is-selected" type="button">Mês</button>
            <button class="chip" type="button">Dois meses</button>
            <button class="chip" type="button">Ano</button>
          </div>
        </div>

        <div class="field">
          <span class="field__label">Categoria</span>
          @php
            $assinaturasCategoria = $categories->firstWhere('name', 'Assinaturas');
          @endphp
          <x-dropdown name="category_id" icon="fa-regular fa-credit-card" up
              :selected="$assinaturasCategoria?->id"
              :options="$categories->where('type', \App\Enums\CategoryType::Expense)->map(fn ($c) => ['value' => $c->id, 'label' => $c->name])" />
        </div>
      </div>

      <div class="modal__foot">
        <button class="btn-ghost" type="button" data-modal-close>Cancelar</button>
        <button class="btn-primary btn-primary--sm" type="submit"><i class="fa-solid fa-check"></i>Salvar assinatura</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ Modal: alterar senha ============ -->
<div class="modal" data-modal="senha" hidden>
  <div class="modal__veil" data-modal-close></div>
  <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="modal-senha-titulo">
    <form method="POST" action="{{ route('password.update') }}">
      @csrf
      @method('PUT')
      <div class="modal__head">
        <div>
          <h2 class="modal__title" id="modal-senha-titulo">Alterar senha</h2>
          <p class="modal__sub">Use uma senha que você ainda não usou em outro lugar.</p>
        </div>
        <button class="modal__close" type="button" aria-label="Fechar" data-modal-close><i class="fa-solid fa-xmark"></i></button>
      </div>

      <div class="modal__body">
        <label class="field">
          <span class="field__label">Senha atual</span>
          <input class="input" type="password" name="current_password" required>
          @error('current_password', 'updatePassword')<span class="field-error">{{ $message }}</span>@enderror
        </label>
        <label class="field">
          <span class="field__label">Nova senha</span>
          <input class="input" type="password" name="password" required>
          @error('password', 'updatePassword')<span class="field-error">{{ $message }}</span>@enderror
        </label>
        <label class="field">
          <span class="field__label">Confirmar nova senha</span>
          <input class="input" type="password" name="password_confirmation" required>
        </label>
      </div>

      <div class="modal__foot">
        <button class="btn-ghost" type="button" data-modal-close>Cancelar</button>
        <button class="btn-primary btn-primary--sm" type="submit"><i class="fa-solid fa-check"></i>Salvar senha</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ Modal: encerrar outras sessões ============ -->
<div class="modal" data-modal="encerrarSessoes" hidden>
  <div class="modal__veil" data-modal-close></div>
  <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="modal-encerrar-sessoes-titulo">
    <form method="POST" action="{{ route('profile.logout-other-sessions') }}">
      @csrf
      @method('PATCH')
      <div class="modal__head">
        <div>
          <h2 class="modal__title" id="modal-encerrar-sessoes-titulo">Encerrar as outras sessões</h2>
          <p class="modal__sub">Todos os outros dispositivos conectados à sua conta vão precisar entrar de novo.</p>
        </div>
        <button class="modal__close" type="button" aria-label="Fechar" data-modal-close><i class="fa-solid fa-xmark"></i></button>
      </div>

      <div class="modal__body">
        <label class="field">
          <span class="field__label">Confirme sua senha</span>
          <input class="input" type="password" name="password" required>
          @error('password')<span class="field-error">{{ $message }}</span>@enderror
        </label>
      </div>

      <div class="modal__foot">
        <button class="btn-ghost" type="button" data-modal-close>Cancelar</button>
        <button class="btn-primary btn-primary--sm" type="submit"><i class="fa-solid fa-check"></i>Encerrar as outras</button>
      </div>
    </form>
  </div>
</div>

<!-- ============ Modal: encerrar conta ============ -->
<div class="modal" data-modal="encerrarConta" hidden>
  <div class="modal__veil" data-modal-close></div>
  <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="modal-encerrar-conta-titulo">
    <form method="POST" action="{{ route('profile.destroy') }}">
      @csrf
      @method('DELETE')
      <div class="modal__head">
        <div>
          <h2 class="modal__title" id="modal-encerrar-conta-titulo">Encerrar conta</h2>
          <p class="modal__sub">Isso apaga sua conta e todos os seus dados. Não dá para desfazer.</p>
        </div>
        <button class="modal__close" type="button" aria-label="Fechar" data-modal-close><i class="fa-solid fa-xmark"></i></button>
      </div>

      <div class="modal__body">
        <label class="field">
          <span class="field__label">Confirme sua senha</span>
          <input class="input" type="password" name="password" required>
          @error('password', 'userDeletion')<span class="field-error">{{ $message }}</span>@enderror
        </label>
      </div>

      <div class="modal__foot">
        <button class="btn-ghost" type="button" data-modal-close>Cancelar</button>
        <button class="btn-danger" type="submit">Encerrar conta</button>
      </div>
    </form>
  </div>
</div>

</x-dashboard-layout>
