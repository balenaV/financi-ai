export function initUi($) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            Accept: 'application/json',
        },
    });

    const openModal = (selector) => {
        const $modal = $(selector);
        $modal.removeClass('hidden');
        $('body').addClass('overflow-hidden');
        setTimeout(() => $modal.find('button, input, select, textarea, a').filter(':visible').first().trigger('focus'), 20);
    };

    const closeModal = ($modal) => {
        $modal.addClass('hidden');
        $('body').removeClass('overflow-hidden');
    };

    $('[data-modal-open]').on('click', function () {
        openModal(`#${$(this).data('modal-open')}`);
    });
    $(document).on('click', '[data-modal-close]', function () {
        closeModal($(this).closest('[role="dialog"]'));
    });
    $(document).on('keydown', (event) => {
        if (event.key === 'Escape') {
            $('[role="dialog"]:visible').each((_, modal) => closeModal($(modal)));
            $('#mobile-sidebar').addClass('hidden');
            $('body').removeClass('overflow-hidden');
        }
    });

    $('[data-sidebar-open]').on('click', () => {
        $('#mobile-sidebar').removeClass('hidden');
        $('body').addClass('overflow-hidden');
    });
    $('[data-sidebar-close]').on('click', () => {
        $('#mobile-sidebar').addClass('hidden');
        $('body').removeClass('overflow-hidden');
    });

    const syncDesktopSidebar = () => {
        const collapsed = document.documentElement.classList.contains('sidebar-collapsed');
        $('[data-desktop-sidebar-toggle]')
            .attr('aria-expanded', String(!collapsed))
            .attr('aria-label', collapsed ? 'Expandir menu lateral' : 'Recolher menu lateral')
            .attr('title', collapsed ? 'Expandir menu' : 'Recolher menu');
    };
    syncDesktopSidebar();
    $('[data-desktop-sidebar-toggle]').on('click', () => {
        document.documentElement.classList.toggle('sidebar-collapsed');
        const collapsed = document.documentElement.classList.contains('sidebar-collapsed');
        localStorage.setItem('financi-sidebar-collapsed', String(collapsed));
        syncDesktopSidebar();
    });

    let pendingForm = null;
    $(document).on('submit', 'form[data-confirm]', function (event) {
        if ($(this).data('confirmed')) return;
        event.preventDefault();
        pendingForm = this;
        $('#confirm-message').text($(this).data('confirm') || 'Deseja continuar?');
        openModal('#confirm-modal');
    });
    $('#confirm-submit').on('click', () => {
        if (!pendingForm) return;
        $(pendingForm).data('confirmed', true);
        pendingForm.requestSubmit();
        pendingForm = null;
        closeModal($('#confirm-modal'));
    });

    $('form:not([data-filter-form])').on('submit', function () {
        const $button = $(this).find('button[type="submit"]').last();
        const loadingText = $button.data('loading-text');
        $button.prop('disabled', true);
        if (loadingText) {
            $button.data('original-text', $button.text()).text(loadingText);
        }
    });

    const toast = (message, type = 'success') => {
        const classes = type === 'success'
            ? 'border-accent-400/30 bg-accent-50 text-accent-800'
            : 'border-red-200 bg-red-50 text-red-800';
        const $toast = $(`<div class="toast max-w-sm rounded-xl border px-4 py-3 text-sm font-medium shadow-lg ${classes}"></div>`).text(message);
        $('#toast-container').append($toast);
        setTimeout(() => $toast.fadeOut(250, () => $toast.remove()), 4500);
    };
    window.financeToast = toast;
    setTimeout(() => $('.toast').fadeOut(250), 4500);

    $('#toggle-values').on('click', function () {
        const $button = $(this).prop('disabled', true);
        $.ajax({ url: $button.data('url'), method: 'PATCH' })
            .done((response) => {
                $('.money-value').each((_, element) => {
                    $(element).text(response.hidden ? element.dataset.hiddenValue : element.dataset.visibleValue);
                });
                const $icon = $button.find('i');
                $icon.toggleClass('fa-eye', !response.hidden).toggleClass('fa-eye-slash', response.hidden);
                $button
                    .attr('data-hidden', String(response.hidden))
                    .attr('aria-label', response.hidden ? 'Exibir valores' : 'Ocultar valores')
                    .attr('title', response.hidden ? 'Exibir valores' : 'Ocultar valores');
                toast(response.message);
            })
            .fail((xhr) => toast(xhr.responseJSON?.message || 'Não foi possível alterar a exibição.', 'error'))
            .always(() => $button.prop('disabled', false));
    });

    $('#toggle-theme').on('click', function () {
        const $button = $(this).prop('disabled', true);
        $.ajax({ url: $button.data('url'), method: 'PATCH' })
            .done((response) => {
                const dark = response.theme === 'dark';
                document.documentElement.classList.toggle('dark', dark);
                document.documentElement.dataset.theme = response.theme;
                localStorage.setItem('financi-theme', response.theme);
                $button.find('i').toggleClass('fa-moon', !dark).toggleClass('fa-sun', dark);
                $button
                    .attr('aria-label', dark ? 'Ativar modo claro' : 'Ativar modo noturno')
                    .attr('title', dark ? 'Modo claro' : 'Modo noturno');
                window.dispatchEvent(new CustomEvent('financi:theme-change', { detail: { theme: response.theme } }));
                toast(response.message);
            })
            .fail((xhr) => toast(xhr.responseJSON?.message || 'Não foi possível alterar o tema.', 'error'))
            .always(() => $button.prop('disabled', false));
    });

    $('[data-tooltip]').each(function () {
        $(this).attr('title', $(this).data('tooltip'));
    });

    const revealElements = [...document.querySelectorAll('[data-reveal]')];

    if (revealElements.length) {
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const revealImmediately = () => revealElements.forEach((element) => element.classList.add('is-visible'));

        if (reducedMotion || !('IntersectionObserver' in window)) {
            revealImmediately();
        } else {
            document.documentElement.classList.add('reveal-enabled');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, {
                rootMargin: '0px 0px -10% 0px',
                threshold: 0.14,
            });

            revealElements.forEach((element) => observer.observe(element));
        }
    }
}
