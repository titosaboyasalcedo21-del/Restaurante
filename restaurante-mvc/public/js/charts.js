// Charts.js - Custom Chart Configurations for RestaurantChain
// This file is loaded via CDN in the layout

// Global chart configuration
Chart.defaults.color = '#6b7280';
Chart.defaults.borderColor = '#e5e7eb';
Chart.defaults.font.family = "'Nunito', 'Segoe UI', sans-serif";

// Chart instances storage
let chartInstances = {};

// Function to initialize all dashboard charts
function initDashboardCharts(data) {
    // Destroy existing charts if any
    Object.values(chartInstances).forEach(chart => {
        if (chart) chart.destroy();
    });
    chartInstances = {};

    // Chart 1: Movements by Type (Pie/Doughnut)
    const movementsTypeCtx = document.getElementById('movementsTypeChart');
    if (movementsTypeCtx) {
        chartInstances.movementsType = new Chart(movementsTypeCtx, {
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
                    backgroundColor: [
                        '#10b981', // green - in
                        '#ef4444', // red - out
                        '#f59e0b', // amber - adjust
                        '#3b82f6'  // blue - transfer
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }

    // Chart 2: Daily Movements (Line)
    const dailyCtx = document.getElementById('dailyMovementsChart');
    if (dailyCtx) {
        chartInstances.daily = new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: data.daily_labels || [],
                datasets: [{
                    label: 'Movimientos',
                    data: data.daily_data || [],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Chart 3: Stock by Branch (Bar)
    const branchCtx = document.getElementById('stockByBranchChart');
    if (branchCtx && data.branch_labels && data.branch_labels.length > 0) {
        chartInstances.branch = new Chart(branchCtx, {
            type: 'bar',
            data: {
                labels: data.branch_labels,
                datasets: [{
                    label: 'Stock Total',
                    data: data.branch_stock || [],
                    backgroundColor: '#10b981',
                    borderRadius: 6,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Chart 4: Products by Category (Pie/Doughnut)
    const categoryCtx = document.getElementById('productsByCategoryChart');
    if (categoryCtx && data.category_labels && data.category_labels.length > 0) {
        chartInstances.category = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: data.category_labels,
                datasets: [{
                    data: data.category_counts || [],
                    backgroundColor: [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444',
                        '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16',
                        '#f97316', '#6366f1'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }
}

// Legacy functions for compatibility
function createCategoryChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.values || [],
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#ef4444',
                    '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                }
            }
        }
    });
}

function createTrendChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Inventario Total',
                data: data.values || [],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createBranchChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Productos',
                data: data.values || [],
                backgroundColor: '#10b981',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createLowStockChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Stock Actual',
                data: data.values || [],
                backgroundColor: data.values.map(v => v <= 5 ? '#ef4444' : '#f59e0b'),
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
}
