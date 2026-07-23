import Chart from 'chart.js/auto';

const palette = ['#534ab7', '#1d9e75', '#dc2626', '#d97706', '#7f77dd', '#0f6e56', '#64748b', '#afa9ec'];

export function initCharts() {
    const dark = document.documentElement.classList.contains('dark');
    Chart.defaults.font.family = "'Instrument Sans', sans-serif";
    Chart.defaults.color = dark ? '#cbd5e1' : '#64748b';

    document.querySelectorAll('canvas[data-chart]').forEach((canvas) => {
        try {
            const config = JSON.parse(canvas.dataset.chart);
            config.data = {
                labels: config.labels || [],
                datasets: (config.datasets || []).map((dataset, index) => ({
                    borderWidth: config.type === 'line' ? 2 : 0,
                    borderRadius: config.type === 'bar' ? 6 : 0,
                    backgroundColor: dataset.backgroundColor || (config.type === 'doughnut' ? palette : palette[index % palette.length]),
                    ...dataset,
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
                    y: { beginAtZero: true, grid: { color: dark ? '#334155' : '#e2e8f0' } },
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
        Chart.defaults.color = isDark ? '#cbd5e1' : '#64748b';
        Object.values(Chart.instances).forEach((chart) => {
            if (chart.options.scales?.y?.grid) {
                chart.options.scales.y.grid.color = isDark ? '#334155' : '#e2e8f0';
            }
            chart.options.color = isDark ? '#cbd5e1' : '#64748b';
            chart.update();
        });
    });
}
