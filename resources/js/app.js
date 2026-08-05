import $ from 'jquery';
import { initUi } from './modules/ui';
import { initForms } from './modules/forms';
import { initCharts } from './modules/charts';
import { initImportWizard } from './modules/import-wizard';

window.$ = window.jQuery = $;

$(() => {
    initUi($);
    initForms($);
    initCharts();
    initImportWizard($);
});
