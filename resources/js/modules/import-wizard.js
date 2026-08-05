const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const postJson = (url, body) => fetch(url, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': csrfToken(),
        Accept: 'application/json',
        ...(typeof body === 'string' ? { 'Content-Type': 'application/json' } : {}),
    },
    body,
}).then((response) => response.json().then((data) => {
    if (! response.ok) throw new Error(data.message ?? 'Falha na requisição.');

    return data;
}));

const getJson = (url) => fetch(url, { headers: { Accept: 'application/json' } }).then((response) => response.json());

export function initImportWizard($) {
    const $root = $('#import-wizard');
    if ($root.length === 0) return;

    const storeUrl = $root.data('store-url');
    const transactionsUrl = $root.data('transactions-url');
    const rowTemplate = document.querySelector('[data-row-template]').content;

    let file = null;
    let batchId = null;
    let format = null;
    let columns = [];
    let rows = [];
    let rowState = new Map();
    let pollTimer = null;

    const showStep = (step) => {
        $root.find('[data-step]').addClass('hidden');
        $root.find(`[data-step="${step}"]`).removeClass('hidden');
        $root.find('[data-step-pill]').each(function () {
            const $pill = $(this);
            const isActive = $pill.data('step-pill') === step;
            $pill.toggleClass('border-(--border-strong) text-(--text-primary)', isActive);
        });
    };

    const setFileInfo = () => {
        if (! file) {
            $root.find('[data-file-info]').addClass('hidden');
            $root.find('[data-action="upload"]').prop('disabled', true);

            return;
        }

        $root.find('[data-file-name]').text(file.name);
        $root.find('[data-file-meta]').text(`${(file.size / 1024).toFixed(0)} KB`);
        $root.find('[data-file-info]').removeClass('hidden').css('display', 'flex');
        $root.find('[data-action="upload"]').prop('disabled', false);
    };

    $root.find('[data-dropzone]').on('dragover', (event) => event.preventDefault());
    $root.find('[data-dropzone]').on('drop', (event) => {
        event.preventDefault();
        const dropped = event.originalEvent.dataTransfer?.files?.[0];
        if (dropped) {
            file = dropped;
            setFileInfo();
        }
    });
    $root.find('[data-file-input]').on('change', (event) => {
        file = event.target.files?.[0] ?? null;
        setFileInfo();
    });
    $root.find('[data-action="clear-file"]').on('click', (event) => {
        event.preventDefault();
        file = null;
        $root.find('[data-file-input]').val('');
        setFileInfo();
    });

    $root.find('[data-action="upload"]').on('click', async () => {
        if (! file) return;

        const body = new FormData();
        body.append('account_id', $root.find('[data-account-select]').val());
        body.append('file', file);

        try {
            const data = await postJson(storeUrl, body);
            batchId = data.batch_id;
            format = data.format;
            columns = data.columns ?? [];

            if (format === 'csv') {
                renderColumnFields(data.suggested_mapping ?? {});
                showStep('mapear');
            } else {
                showStep('revisar');
                startParse();
            }
        } catch (error) {
            window.financeToast?.(error.message, 'danger');
        }
    });

    const columnLabels = { date: 'Data do lançamento', description: 'Descrição', amount: 'Valor', external_id: 'Identificador (opcional)' };

    function renderColumnFields(suggested) {
        const $wrap = $root.find('[data-column-fields]').empty();
        Object.entries(columnLabels).forEach(([field, label]) => {
            const $select = $(`<select class="form-control" data-column-field="${field}"></select>`);
            columns.forEach((column) => $select.append(`<option value="${column}">${column}</option>`));
            if (suggested[field]) $select.val(suggested[field]);
            $wrap.append($(`<label class="block"><span class="form-label">${label}</span></label>`).append($select));
        });
    }

    $root.find('[data-action="back-upload"]').on('click', () => showStep('upload'));
    $root.find('[data-action="back-mapear"]').on('click', () => showStep('mapear'));

    $root.find('[data-action="parse"]').on('click', () => {
        showStep('revisar');
        startParse();
    });

    async function startParse() {
        $root.find('[data-parsing-state]').removeClass('hidden');
        $root.find('[data-review-content]').addClass('hidden');

        const columnMap = {};
        $root.find('[data-column-field]').each(function () {
            columnMap[$(this).data('column-field')] = $(this).val();
        });

        try {
            await postJson(`${transactionsUrl}/import/${batchId}/preview`, JSON.stringify({
                column_map: format === 'csv' ? columnMap : null,
                date_format: $root.find('[data-date-format]').val(),
                decimal_separator: $root.find('[data-decimal-separator]').val(),
            }));
            pollStatus();
        } catch (error) {
            window.financeToast?.(error.message, 'danger');
        }
    }

    function pollStatus() {
        clearTimeout(pollTimer);
        getJson(`${transactionsUrl}/import/${batchId}`).then((data) => {
            if (data.status === 'parsing' || data.status === 'pending') {
                pollTimer = setTimeout(pollStatus, 1200);

                return;
            }

            if (data.status === 'failed') {
                window.financeToast?.(data.error ?? 'Falha ao ler o arquivo.', 'danger');
                showStep('mapear');

                return;
            }

            rows = data.rows;
            rowState = new Map(rows.map((row) => [row.id, { included: row.included, categoryId: row.suggested_category_id }]));
            renderReview();
        });
    }

    function renderReview() {
        $root.find('[data-parsing-state]').addClass('hidden');
        $root.find('[data-review-content]').removeClass('hidden');

        const duplicates = rows.filter((row) => row.status === 'duplicate_exact' || row.status === 'duplicate_probable').length;
        const summary = [
            { label: 'Lançamentos lidos', value: rows.length },
            { label: 'Já existem na conta', value: duplicates },
        ];
        $root.find('[data-summary]').empty().append(summary.map((item) => `
            <div class="surface p-4"><div class="text-xs font-semibold text-slate-500">${item.label}</div><div class="mt-1 text-2xl font-extrabold tracking-tight">${item.value}</div></div>
        `).join(''));

        const $rows = $root.find('[data-rows]').empty();
        rows.forEach((row) => {
            const $row = $(rowTemplate.cloneNode(true));
            const state = rowState.get(row.id);
            $row.find('[data-row-check]').prop('checked', state.included).on('change', function () {
                rowState.get(row.id).included = this.checked;
                updateSelectedText();
            });
            $row.find('[data-row-date]').text(row.posted_at);
            $row.find('[data-row-desc]').text(row.description);
            $row.find('[data-row-category]').val(state.categoryId ?? '').on('change', function () {
                rowState.get(row.id).categoryId = this.value || null;
            });
            $row.find('[data-row-amount]').text((row.type === 'expense' ? '− ' : '+ ') + 'R$ ' + Number(row.amount).toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
            if (row.status === 'duplicate_exact' || row.status === 'duplicate_probable') {
                $row.find('[data-row]').css('opacity', 0.6);
                $row.find('[data-row-badge]').html(`<span class="ml-2 rounded-full bg-warning-50 px-2 py-0.5 text-xs font-bold text-warning-700">${row.status_label}</span>`);
            }
            $rows.append($row);
        });

        updateSelectedText();
    }

    function updateSelectedText() {
        const included = [...rowState.values()].filter((s) => s.included).length;
        $root.find('[data-selected-text]').text(`${included} de ${rows.length} serão importados`);
        $root.find('[data-commit-label]').text(`Importar ${included} lançamentos`);
    }

    $root.find('[data-select-all]').on('change', function () {
        const checked = this.checked;
        rowState.forEach((state) => { state.included = checked; });
        renderReview();
    });

    $root.find('[data-action="commit"]').on('click', async () => {
        const rowIds = [...rowState.entries()].filter(([, s]) => s.included).map(([id]) => Number(id));
        const categories = Object.fromEntries([...rowState.entries()].filter(([, s]) => s.included && s.categoryId).map(([id, s]) => [id, s.categoryId]));

        if (rowIds.length === 0) {
            window.financeToast?.('Selecione ao menos um lançamento.', 'danger');

            return;
        }

        try {
            const result = await postJson(`${transactionsUrl}/import/${batchId}/commit`, JSON.stringify({ row_ids: rowIds, categories }));
            renderConcluido(result);
            showStep('concluido');
        } catch (error) {
            window.financeToast?.(error.message, 'danger');
        }
    });

    function renderConcluido(result) {
        $root.find('[data-final-title]').text(`${result.imported} lançamento${result.imported === 1 ? '' : 's'} importado${result.imported === 1 ? '' : 's'}.`);
        $root.find('[data-final-detail]').text(`${result.skipped} duplicado(s) ignorado(s). Seu painel está atualizado.`);
        $root.find('[data-final-summary]').empty().append([
            { label: 'Importados', value: result.imported },
            { label: 'Duplicados ignorados', value: result.skipped },
            { label: 'Categorizados automaticamente', value: result.auto_categorized },
        ].map((item) => `
            <div class="surface p-4"><div class="text-xs font-semibold text-slate-500">${item.label}</div><div class="mt-1 text-2xl font-extrabold tracking-tight">${item.value}</div></div>
        `).join(''));
    }

    $root.find('[data-action="restart"]').on('click', () => {
        file = null;
        batchId = null;
        rows = [];
        rowState = new Map();
        $root.find('[data-file-input]').val('');
        setFileInfo();
        showStep('upload');
    });

    setFileInfo();
    showStep('upload');
}
