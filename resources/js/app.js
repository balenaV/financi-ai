import $ from 'jquery';
import { initUi } from './modules/ui';
import { initForms } from './modules/forms';
import { initCharts } from './modules/charts';
import { initAuthTabs } from './modules/auth-tabs';

window.$ = window.jQuery = $;

$(() => {
    initUi($);
    initForms($);
    initCharts();
    initAuthTabs($);
});
