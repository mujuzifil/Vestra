import { Chart, LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip } from 'chart.js';

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip);

function initDashboardChart() {
    const payload = window.__dashboardChartData?.['sales-overview-chart'];
    if (!payload) return;

    const canvas = document.getElementById('sales-overview-chart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const primaryColor = '#0d3b66';
    const primaryFill = 'rgba(13, 59, 102, 0.08)';

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: payload.labels,
            datasets: [
                {
                    label: 'Quote Value (UGX)',
                    data: payload.values,
                    fill: true,
                    tension: 0.4,
                    borderColor: primaryColor,
                    backgroundColor: primaryFill,
                    pointBackgroundColor: primaryColor,
                    pointBorderColor: '#ffffff',
                    pointHoverBackgroundColor: '#70c050',
                    pointHoverBorderColor: '#ffffff',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleColor: '#f8fafc',
                    bodyColor: '#f8fafc',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: (context) => 'UGX ' + Number(context.parsed.y).toLocaleString(),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: {
                        color: 'rgba(226, 232, 240, 0.6)',
                        drawBorder: false,
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11, family: "'Poppins', sans-serif" },
                        callback: (value) => 'UGX ' + (Number(value) / 1000) + 'k',
                    },
                },
                x: {
                    border: { display: false },
                    grid: { display: false },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11, family: "'Poppins', sans-serif" },
                    },
                },
            },
        },
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboardChart);
} else {
    initDashboardChart();
}

document.addEventListener('livewire:navigated', initDashboardChart);
