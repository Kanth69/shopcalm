function initializeDashboardCharts(chartData) {
    const revenueOrdersCtx = document.getElementById('revenueOrdersChart');
    if (revenueOrdersCtx) {
        new Chart(revenueOrdersCtx, {
            type: 'line',
            data: {
                labels: chartData.order_trend.labels,
                datasets: [{
                    label: 'Orders',
                    data: chartData.order_trend.values,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    yAxisID: 'y',
                    fill: true,
                }, {
                    label: 'Revenue',
                    data: chartData.revenue_trend.values,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    yAxisID: 'y1',
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Orders' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Revenue (₹)' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    }

    const orderStatusCtx = document.getElementById('orderStatusChart');
    if (orderStatusCtx) {
        new Chart(orderStatusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(chartData.order_status_distribution),
                datasets: [{
                    label: 'Order Status',
                    data: Object.values(chartData.order_status_distribution),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                }
            }
        });
    }
}

// AJAX auto-refresh for stats
setInterval(function() {
    fetch('{{ route("admin.dashboard.stats") }}' + window.location.search)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('dashboard-stats-container');
            if(container) {
                // This is a simplified update. A more robust solution might involve
                // creating a new element and replacing the old one to avoid FOUC.
                // For now, we just update the values.
                Object.keys(data).forEach(key => {
                    const element = container.querySelector(`[data-stat="${key}"]`);
                    if(element) {
                        let value = data[key];
                        if(key === 'total_revenue' || key === 'avg_order_value') {
                            value = '₹' + parseFloat(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        } else {
                            value = parseInt(value).toLocaleString('en-IN');
                        }
                        element.textContent = value;
                    }
                });
            }
        })
        .catch(error => console.error('Error fetching dashboard stats:', error));
}, 60000); // Refresh every 60 seconds
