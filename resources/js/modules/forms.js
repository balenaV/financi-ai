export function initForms($) {
    $('select[name="account_id"][required]').each(function () {
        const options = $(this).find('option[value!=""]:not(:disabled)');
        if (!$(this).val() && options.length === 1) {
            $(this).val(options.first().val()).trigger('change');
        }
    });

    const updateTransactionFields = () => {
        const type = $('#transaction-type').val();
        const transfer = type === 'transfer';
        const channelSelect = $('#payment-channel');
        const cardOption = channelSelect.find('option[value="credit_card"]');

        cardOption.prop('disabled', type !== 'expense');
        if (type !== 'expense' && channelSelect.val() === 'credit_card') {
            channelSelect.val('account');
        }
        if (transfer) channelSelect.val('account');

        const creditCard = channelSelect.val() === 'credit_card' && type === 'expense';
        $('[data-transfer-field]').toggleClass('hidden', !transfer);
        $('[data-category-field]').toggleClass('hidden', transfer);
        $('[data-payment-channel-field]').toggleClass('hidden', transfer);
        $('[data-account-field]').toggleClass('hidden', creditCard);
        $('[data-credit-card-field]').toggleClass('hidden', !creditCard);
        $('[name="destination_account_id"]').prop('required', transfer);
        $('[name="category_id"]').prop('required', !transfer);
        $('[name="account_id"]').prop('required', !creditCard);
        $('[name="credit_card_id"]').prop('required', creditCard);

        $('[name="category_id"] option[data-type]').each(function () {
            const visible = $(this).data('type') === type;
            $(this).prop('hidden', !visible).prop('disabled', !visible);
        });
        const selected = $('[name="category_id"] option:selected');
        if (selected.data('type') && selected.data('type') !== type) {
            $('[name="category_id"]').val('');
        }
    };

    const updatePaymentFields = () => {
        const installment = $('#payment-mode').val() === 'installment';
        $('[data-installment-fields]').toggleClass('hidden', !installment);
        $('[data-recurrence-fields]').toggleClass('hidden', installment);
        $('[name="installment_count"], [name="first_installment_date"]').prop('required', installment);
        if (installment) $('[name="recurrence_count"]').val(1);
    };

    $('#transaction-type').on('change', updateTransactionFields);
    $('#payment-channel').on('change', updateTransactionFields);
    $('#payment-mode').on('change', updatePaymentFields);
    updateTransactionFields();
    updatePaymentFields();

    $('[data-money-input]').on('blur', function () {
        let value = String($(this).val() || '').trim().replace(/[^\d,.-]/g, '');
        if (!value) return;
        if (value.includes(',') && value.includes('.')) {
            value = value.replace(/\./g, '').replace(',', '.');
        } else {
            value = value.replace(',', '.');
        }
        const number = Number(value);
        if (Number.isFinite(number)) {
            $(this).val(number.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }
    });

    $('[data-filter-form] select').on('change', function () {
        if ($(this).closest('form').data('auto-submit')) $(this).closest('form').trigger('submit');
    });
}
