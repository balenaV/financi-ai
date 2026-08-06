<x-import-layout title="Importar extrato">

<header class="import-page__header">
  <div class="import-page__header-inner">
    <a class="back-link" href="{{ route('dashboard') }}#contas"><i class="fa-solid fa-arrow-left"></i>Voltar para Contas</a>
    <button class="btn-icon" type="button" data-theme-toggle data-toggle-url="{{ route('settings.toggle-theme') }}" aria-label="Alternar tema"><i class="fa-solid fa-moon"></i></button>
  </div>
</header>

<main class="import-page__main">
  <div class="import-page__inner" id="import-wizard" data-store-url="{{ route('transactions.import.store') }}" data-transactions-url="{{ url('/transactions') }}" data-export-csv-url="{{ route('transactions.export') }}" data-export-ofx-url="{{ route('transactions.export.ofx') }}" data-dashboard-url="{{ route('dashboard') }}">

    <h1 class="import-page__title">Importar extrato</h1>
    <p class="import-page__sub">Traga seus lançamentos do banco sem digitar um por um.</p>

    <ol class="steps-trail">
      <li data-trail="upload" data-trail-icon="fa-solid fa-file-arrow-up" data-state="current">
        <span class="trail-step" data-state="current">
          <span class="trail-step__bubble"><i class="fa-solid fa-file-arrow-up"></i></span>
          <span class="trail-step__name">Arquivo</span>
        </span>
        <span class="steps-trail__dash"></span>
      </li>
      <li data-trail="mapear" data-trail-icon="fa-solid fa-table-columns" data-state="todo">
        <span class="trail-step" data-state="todo">
          <span class="trail-step__bubble"><i class="fa-solid fa-table-columns"></i></span>
          <span class="trail-step__name">Colunas</span>
        </span>
        <span class="steps-trail__dash"></span>
      </li>
      <li data-trail="revisar" data-trail-icon="fa-solid fa-list-check" data-state="todo">
        <span class="trail-step" data-state="todo">
          <span class="trail-step__bubble"><i class="fa-solid fa-list-check"></i></span>
          <span class="trail-step__name">Revisão</span>
        </span>
        <span class="steps-trail__dash"></span>
      </li>
      <li data-trail="concluido" data-trail-icon="fa-solid fa-check" data-state="todo">
        <span class="trail-step" data-state="todo">
          <span class="trail-step__bubble"><i class="fa-solid fa-check"></i></span>
          <span class="trail-step__name">Pronto</span>
        </span>
      </li>
    </ol>

    <!-- ============ Etapa 1: arquivo ============ -->
    <div class="import-step" data-step="upload">

      <section class="card">
        <div class="destination">
          <span class="destination__label">Importar para</span>
          <label class="field-select">
            <i class="fa-solid fa-building-columns"></i>
            <select aria-label="Conta de destino" data-account-select>
              @foreach($accounts as $account)
                <option value="{{ $account->id }}">{{ $account->name }}</option>
              @endforeach
            </select>
            <i class="fa-solid fa-chevron-down field-select__chevron"></i>
          </label>
        </div>

        <label class="dropzone" data-dropzone data-dragging="false">
          <span class="dropzone__icon"><i class="fa-solid fa-file-arrow-up"></i></span>
          <span class="dropzone__title">Arraste seu extrato aqui</span>
          <span class="dropzone__hint">Ou clique para escolher um arquivo. Aceitamos OFX e CSV, até 10 MB.</span>
          <span class="dropzone__cta">Escolher arquivo</span>
          <input type="file" accept=".ofx,.csv" data-file-input>
        </label>

        <div class="file-chip" data-file-chip hidden>
          <span class="file-chip__icon"><i class="fa-solid fa-file-lines"></i></span>
          <span class="file-chip__body">
            <span class="file-chip__name" data-file-name></span>
            <span class="file-chip__meta" data-file-meta></span>
          </span>
          <button class="file-chip__remove" type="button" data-file-remove aria-label="Remover arquivo"><i class="fa-solid fa-xmark"></i></button>
        </div>
      </section>

      <section class="card card--tight">
        <div class="tutorial__head">
          <h2 class="card__title card__title--sm">Onde encontrar seu extrato</h2>
          <label class="field-select">
            <i class="fa-solid fa-building-columns"></i>
            <select aria-label="Seu banco" data-bank-select>
              <option>Nubank</option>
              <option>Itaú</option>
              <option>Bradesco</option>
              <option>Banco do Brasil</option>
              <option>Caixa</option>
              <option>Santander</option>
              <option>Inter</option>
              <option>C6 Bank</option>
              <option>Outro banco</option>
            </select>
            <i class="fa-solid fa-chevron-down field-select__chevron"></i>
          </label>
        </div>

        <ol class="tutorial__steps" data-bank-steps></ol>

        <p class="note note--boxed" data-bank-note>
          <i class="fa-solid fa-circle-info"></i><span data-bank-note-text></span>
        </p>
        <p class="note note--safe">
          <i class="fa-solid fa-shield-halved"></i>O arquivo é lido para extrair os lançamentos e descartado em seguida. O financiaí nunca pede a senha do seu banco.
        </p>
        <p class="note note--fine">Os caminhos podem mudar conforme atualizações do app ou do site do banco.</p>
      </section>

      <div class="step-nav step-nav--end">
        <button class="btn-step-next" type="button" data-step-goto="mapear" data-continue disabled>Continuar<i class="fa-solid fa-arrow-right"></i></button>
      </div>
    </div>

    <!-- ============ Etapa 2: colunas (só para CSV) ============ -->
    <div class="import-step" data-step="mapear" hidden>

      <section class="card">
        <h2 class="card__title">Conferir as colunas</h2>
        <p class="card__text">Cada banco exporta o CSV de um jeito. Diga qual coluna do arquivo corresponde a cada campo.</p>

        <div class="map-grid" data-column-fields></div>

        <div class="map-grid map-grid--divided">
          <label class="map-field">
            <span class="map-field__label">Formato de data</span>
            <span class="field-select field-select--block">
              <select aria-label="Formato de data" data-date-format>
                <option value="DD/MM/AAAA">DD/MM/AAAA</option>
                <option value="AAAA-MM-DD">AAAA-MM-DD</option>
                <option value="MM/DD/AAAA">MM/DD/AAAA</option>
              </select>
              <i class="fa-solid fa-chevron-down field-select__chevron"></i>
            </span>
          </label>
          <label class="map-field">
            <span class="map-field__label">Separador decimal</span>
            <span class="field-select field-select--block">
              <select aria-label="Separador decimal" data-decimal-separator>
                <option value="virgula">Vírgula (1.234,56)</option>
                <option value="ponto">Ponto (1,234.56)</option>
              </select>
              <i class="fa-solid fa-chevron-down field-select__chevron"></i>
            </span>
          </label>
        </div>
      </section>

      <div class="step-nav">
        <button class="btn-step-back" type="button" data-step-goto="upload"><i class="fa-solid fa-arrow-left"></i>Voltar</button>
        <button class="btn-step-next" type="button" data-action="parse">Ler lançamentos<i class="fa-solid fa-arrow-right"></i></button>
      </div>
    </div>

    <!-- ============ Etapa 3: revisão ============ -->
    <div class="import-step" data-step="revisar" hidden>

      <div class="card" data-parsing-state>
        <p class="card__text card__text--flush"><i class="fa-solid fa-spinner fa-spin"></i>&nbsp; Lendo o arquivo...</p>
      </div>

      <div class="import-step" data-review-content hidden>
        <div class="summary-grid" data-summary></div>

        <div class="review-filters" data-review-filters>
          <button class="filter-pill" type="button" data-filter="todos" aria-pressed="true">Todos<span class="filter-pill__count" data-count="todos"></span></button>
          <button class="filter-pill" type="button" data-filter="duplicado" aria-pressed="false">Duplicados<span class="filter-pill__count" data-count="duplicado"></span></button>
          <button class="filter-pill" type="button" data-filter="revisar" aria-pressed="false">Sem categoria<span class="filter-pill__count" data-count="revisar"></span></button>
          <span class="review-filters__status" data-review-status></span>
        </div>

        <section class="card card--flush">
          <div class="review-head">
            <input class="entry__check" type="checkbox" data-check-all checked aria-label="Selecionar todos">
            <span class="review-head__label">Lançamento</span>
            <span class="review-head__value">Valor</span>
          </div>
          <div data-entries></div>
        </section>
      </div>

      <div class="step-nav">
        <button class="btn-step-back" type="button" data-step-goto="mapear"><i class="fa-solid fa-arrow-left"></i>Voltar</button>
        <button class="btn-step-next" type="button" data-action="commit"><i class="fa-solid fa-check"></i><span data-confirm-label>Importar lançamentos</span></button>
      </div>
    </div>

    <!-- ============ Etapa 4: concluído ============ -->
    <div class="import-step" data-step="concluido" hidden>

      <section class="done-hero">
        <span class="done-hero__ring" aria-hidden="true"></span>
        <div class="done-hero__body">
          <span class="done-hero__badge"><i class="fa-solid fa-check"></i>Importação concluída</span>
          <h2 class="done-hero__title" data-final-title></h2>
          <p class="done-hero__text" data-final-detail></p>
        </div>
        <img class="done-hero__art" src="{{ asset('design/assets/capi/capi-comemorando.png') }}" alt="">
      </section>

      <div class="summary-grid summary-grid--final" data-final-summary></div>

      <section class="export-card">
        <div>
          <h2 class="card__title card__title--sm">Seus dados continuam seus</h2>
          <p>Exporte tudo o que você registrou em CSV ou OFX quando quiser, sem pedir autorização a ninguém.</p>
        </div>
        <div class="export-card__actions">
          <a class="btn-export" href="{{ route('transactions.export') }}"><i class="fa-solid fa-file-csv"></i>Exportar CSV</a>
          <a class="btn-export" href="{{ route('transactions.export.ofx') }}"><i class="fa-solid fa-file-arrow-down"></i>Exportar OFX</a>
        </div>
      </section>

      <div class="step-nav">
        <button class="btn-step-back" type="button" data-restart><i class="fa-solid fa-rotate-left"></i>Importar outro extrato</button>
        <a class="btn-step-next" href="{{ route('dashboard') }}#contas">Ver meu painel<i class="fa-solid fa-arrow-right"></i></a>
      </div>
    </div>

  </div>
</main>

<template data-entry-template>
  <div class="entry" data-entry>
    <input class="entry__check" type="checkbox" aria-label="Incluir lançamento">
    <span class="entry__date" data-entry-date></span>
    <span class="entry__body">
      <span class="entry__title-row">
        <span class="entry__name" data-entry-name></span>
        <span class="entry__flag" data-entry-flag hidden></span>
      </span>
      <span class="entry__source" data-entry-source></span>
    </span>
    <span class="entry__category">
      <select aria-label="Categoria" data-entry-category>
        <option value="">Sem categoria</option>
        @foreach($categories as $category)
          <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
      </select>
      <i class="fa-solid fa-chevron-down"></i>
    </span>
    <span class="entry__value" data-entry-value></span>
  </div>
</template>

</x-import-layout>
