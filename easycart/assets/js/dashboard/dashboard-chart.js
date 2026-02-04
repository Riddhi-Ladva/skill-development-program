/**
 * Premium Dashboard Chart Logic
 * 
 * Responsibility: Fetches data and renders a smooth area chart using Chart.js.
 */

document.addEventListener('DOMContentLoaded', function () {
    const chartCtx = document.getElementById('spendingChart');
    if (!chartCtx) return;

    const noDataMsg = document.getElementById('no-data-message');
    const ctx = chartCtx.getContext('2d');

    // Create Gradient for fill
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)'); // Primary color light
    gradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

    // Fetch data from refined API
    fetch(window.EasyCart.baseUrl + '/ajax/dashboard/order_spending.php')
        .then(response => response.json())
        .then(res => {
            if (!res.success || !res.data || res.data.length === 0) {
                if (noDataMsg) noDataMsg.style.display = 'flex';
                if (chartCtx) chartCtx.style.display = 'none';
                return;
            }

            // Show canvas
            if (noDataMsg) noDataMsg.style.display = 'none';
            chartCtx.style.display = 'block';

            const labels = res.data.map(item => item.date);
            const values = res.data.map(item => item.amount);

            new Chart(chartCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Spending',
                        data: values,
                        borderColor: '#2563eb', // --color-primary
                        backgroundColor: gradient,
                        borderWidth: 3,
                        tension: 0.4, // Smooth curves
                        pointRadius: 5,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#2563eb',
                        pointBorderWidth: 2,
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: '#2563eb',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    return ' Spent: $' + context.parsed.y.toLocaleString(undefined, { minimumFractionDigits: 2 });
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9',
                                drawBorder: false
                            },
                            ticks: {
                                callback: function (value) {
                                    return '$' + value;
                                },
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Chart Load Error:', error);
            if (noDataMsg) {
                noDataMsg.textContent = 'Unable to load spending overview.';
                noDataMsg.style.display = 'flex';
            }
        });
});
