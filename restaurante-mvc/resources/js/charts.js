// Dashboard Charts - Works with Chart.js from CDN
// This file is loaded inline in the dashboard

function initDashboardCharts(data) {
    // Check if Chart is available
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js not loaded');
        return;
    }

    // Global chart configuration
    Chart.defaults.font.family = "'DM Sans', 'Segoe UI', sans-serif";
    Chart.defaults.color = '#6b7280';

    // Movements by type pie chart
    const movementsTypeCtx = document.getElementById('movementsTypeChart');
    if (movementsTypeCtx) {
        new Chart(movementsTypeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Entradas', 'Salidas', 'Ajustes', 'Transferencias'],
                datasets: [{
                    data: [
                        data.movements_in || 0,
                        data.movements_out || 0,
                        data.movements_adjust || 0,
                        data.movements_transfer || 0
                    ],
                    backgroundColor: ['#10b981', '#ef4444', '#f59e0b', '#6366f1'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // Daily movements line chart (last 7 days)
    const dailyMovementsCtx = document.getElementById('dailyMovementsChart');
    if (dailyMovementsCtx) {
        new Chart(dailyMovementsCtx, {
            type: 'line',
            data: {
                labels: data.daily_labels || [],
                datasets: [{
                    label: 'Movimientos',
                    data: data.daily_data || [],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Stock by branch bar chart
    const stockByBranchCtx = document.getElementById('stockByBranchChart');
    if (stockByBranchCtx) {
        new Chart(stockByBranchCtx, {
            type: 'bar',
            data: {
                labels: data.branch_labels || [],
                datasets: [{
                    label: 'Stock Total',
                    data: data.branch_stock || [],
                    backgroundColor: '#10b981',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Products by category pie chart
    const productsByCategoryCtx = document.getElementById('productsByCategoryChart');
    if (productsByCategoryCtx) {
        new Chart(productsByCategoryCtx, {
            type: 'pie',
            data: {
                labels: data.category_labels || [],
                datasets: [{
                    data: data.category_counts || [],
                    backgroundColor: [
                        '#6366f1', '#10b981', '#f59e0b', '#ef4444',
                        '#8b5cf6', '#06b6d4', '#ec4899', '#84cc16'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    }
}

// Make function available globally
window.initDashboardCharts = initDashboardCharts;
