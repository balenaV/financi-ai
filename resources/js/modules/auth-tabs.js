export function initAuthTabs($) {
    const $switch = $('[data-auth-tabs]');
    if ($switch.length === 0) return;

    const setMode = (mode) => {
        $switch.attr('data-active', mode);
        $('[data-tab-panel]').each(function () {
            const active = $(this).attr('data-tab-panel') === mode;
            if (active) $(this).removeAttr('data-hidden');
            else $(this).attr('data-hidden', '');
        });
        $('[data-mode]').each(function () {
            $(this).toggleClass('hidden', $(this).attr('data-mode') !== mode);
        });
        $('[data-tab-button]').each(function () {
            $(this).attr('aria-selected', String($(this).attr('data-tab-button') === mode));
        });
        history.replaceState(null, '', mode === 'register' ? '/register' : '/login');
    };

    $('[data-tab-button]').on('click', function () {
        setMode($(this).attr('data-tab-button'));
    });

    setMode($switch.attr('data-initial-mode') || 'login');
}
