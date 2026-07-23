import $ from 'jquery';
import { initUi } from './modules/ui';
import { initForms } from './modules/forms';
import { initCharts } from './modules/charts';

window.$ = window.jQuery = $;

$(() => {
    initUi($);
    initForms($);
    initCharts();
});
