import Chart from 'chart.js/auto';

const cssColor = (token, fallback) => (
    getComputedStyle(document.documentElement).getPropertyValue(token).trim() || fallback
);

const chartPalette = () => [
    cssColor('--blue-600', '#2563eb'),
    cssColor('--teal-500', '#14b89a'),
    cssColor('--red-600', '#dc2626'),
    cssColor('--orange-500', '#f97316'),
    cssColor('--violet-600', '#7c3aed'),
    cssColor('--teal-700', '#0f766e'),
    cssColor('--text-tertiary', '#64748b'),
    cssColor('--blue-400', '#60a5fa'),
];

const legacyColorTokens = {
    '#1d9e75': '--teal-500',
    '#dc2626': '--red-600',
    '#534ab7': '--blue-600',
    '#eeedfe': '--blue-100',
};

const tokenForConfiguredColor = (color) => (
    typeof color === 'string' ? legacyColorTokens[color.toLowerCase()] : undefined
);

const normalizeDatasetColors = (dataset, fallbackColor) => {
    const normalized = {
        backgroundColor: dataset.backgroundColor || fallbackColor,
        ...dataset,
    };
    const backgroundToken = tokenForConfiguredColor(dataset.backgroundColor);
    const borderToken = tokenForConfiguredColor(dataset.borderColor);

    if (backgroundToken) {
        normalized.backgroundColor = cssColor(backgroundToken, dataset.backgroundColor);
        normalized.financiBackgroundToken = backgroundToken;
    }
    if (borderToken) {
        normalized.borderColor = cssColor(borderToken, dataset.borderColor);
        normalized.financiBorderToken = borderToken;
    }

    return normalized;
};

export function initCharts() {
    const dark = document.documentElement.classList.contains('dark');
    Chart.defaults.font.family = "'Instrument Sans', sans-serif";
    Chart.defaults.color = cssColor('--text-secondary', dark ? '#c7cbd1' : '#475569');

    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        try {
            const config = JSON.parse(canvas.dataset.chart);
            const palette = chartPalette();
            config.data = {
                labels: config.labels || [],
                datasets: (config.datasets || []).map((dataset, index) => ({
                    borderWidth: config.type === 'line' ? 2 : 0,
                    borderRadius: config.type === 'bar' ? 6 : 0,
                    ...normalizeDatasetColors(
                        dataset,
                        config.type === 'doughnut' ? palette : palette[index % palette.length],
                    ),
                })),
            };
            delete config.labels;
            delete config.datasets;
            config.options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 18 } },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.dataset.label ? `${context.dataset.label}: ` : ''}${Number(context.raw).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}`,
                        },
                    },
                },
                scales: config.type === 'doughnut' ? undefined : {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { color: cssColor('--border-subtle', dark ? '#28313b' : '#e5e2d6') } },
                },
                ...config.options,
            };
            new Chart(canvas, config);
        } catch (error) {
            console.error('Não foi possível renderizar o gráfico.', error);
        }
    });

    window.addEventListener('financi:theme-change', ({ detail }) => {
        const isDark = detail.theme === 'dark';
        Chart.defaults.color = cssColor('--text-secondary', isDark ? '#c7cbd1' : '#475569');
        Object.values(Chart.instances).forEach((chart) => {
            chart.data.datasets.forEach((dataset) => {
                if (dataset.financiBackgroundToken) {
                    dataset.backgroundColor = cssColor(dataset.financiBackgroundToken, dataset.backgroundColor);
                }
                if (dataset.financiBorderToken) {
                    dataset.borderColor = cssColor(dataset.financiBorderToken, dataset.borderColor);
                }
            });
            if (chart.options.scales?.y?.grid) {
                chart.options.scales.y.grid.color = cssColor('--border-subtle', isDark ? '#28313b' : '#e5e2d6');
            }
            chart.options.color = cssColor('--text-secondary', isDark ? '#c7cbd1' : '#475569');
            chart.update();
        });
    });
}
